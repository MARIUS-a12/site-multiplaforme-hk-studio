<?php

namespace App\Support\Horaires;

use Carbon\CarbonImmutable;

/**
 * "Ouvert jusqu'à 19h" ou "Fermé — ouvre demain à 8h", pour le pied de page
 * de la vitrine (voir EtablissementVitrineResource) — toujours en heure
 * d'Abidjan, jamais celle du serveur ni celle du visiteur. $horaires est le
 * JSON stocké sur Etablissement::horaires : une clé par jour (lundi..dimanche),
 * chacune {ouverture, fermeture, ferme}.
 *
 * Un horaire qui traverse minuit (ex. samedi 20:00 -> 02:00) est détecté par
 * fermeture < ouverture : la fermeture tombe alors le lendemain, et la
 * session d'hier peut donc encore être en cours aujourd'hui tôt le matin —
 * c'est pour ça qu'on regarde hier ET aujourd'hui, jamais le seul jour
 * courant.
 */
final class EtatHoraires
{
    private const JOURS = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];

    /**
     * @param  array<string, array{ouverture: ?string, fermeture: ?string, ferme: bool}>|null  $horaires
     * @return array{ouvert: bool, libelle: string}|null
     */
    public static function calculer(?array $horaires, ?CarbonImmutable $maintenant = null): ?array
    {
        if ($horaires === null) {
            return null;
        }

        $maintenant = ($maintenant ?? CarbonImmutable::now())->setTimezone('Africa/Abidjan');
        $indexAujourdhui = $maintenant->dayOfWeekIso - 1;
        $indexHier = ($indexAujourdhui + 6) % 7;

        $horaireHier = self::jourOuvert($horaires, self::JOURS[$indexHier]);

        if ($horaireHier !== null && self::traverseMinuit($horaireHier)) {
            $fermetureHier = $maintenant->setTimeFromTimeString($horaireHier['fermeture']);

            if ($maintenant->lessThan($fermetureHier)) {
                return self::ouvert($horaireHier['fermeture']);
            }
        }

        $jourAujourdhui = self::JOURS[$indexAujourdhui];
        $horaireAujourdhui = self::jourOuvert($horaires, $jourAujourdhui);

        if ($horaireAujourdhui !== null) {
            $ouverture = $maintenant->setTimeFromTimeString($horaireAujourdhui['ouverture']);
            $fermeture = self::traverseMinuit($horaireAujourdhui)
                ? $maintenant->addDay()->setTimeFromTimeString($horaireAujourdhui['fermeture'])
                : $maintenant->setTimeFromTimeString($horaireAujourdhui['fermeture']);

            if ($maintenant->greaterThanOrEqualTo($ouverture) && $maintenant->lessThan($fermeture)) {
                return self::ouvert($horaireAujourdhui['fermeture']);
            }

            if ($maintenant->lessThan($ouverture)) {
                return self::fermeJusqua("aujourd'hui", $horaireAujourdhui['ouverture']);
            }
        }

        for ($decalage = 1; $decalage <= 7; $decalage++) {
            $jour = self::JOURS[($indexAujourdhui + $decalage) % 7];
            $horaire = self::jourOuvert($horaires, $jour);

            if ($horaire !== null) {
                return self::fermeJusqua($decalage === 1 ? 'demain' : $jour, $horaire['ouverture']);
            }
        }

        return ['ouvert' => false, 'libelle' => 'Fermé'];
    }

    /**
     * @return array{ouverture: string, fermeture: string, ferme: bool}|null
     */
    private static function jourOuvert(array $horaires, string $jour): ?array
    {
        $entree = $horaires[$jour] ?? null;

        if ($entree === null || ($entree['ferme'] ?? true) || empty($entree['ouverture']) || empty($entree['fermeture'])) {
            return null;
        }

        return $entree;
    }

    private static function traverseMinuit(array $horaire): bool
    {
        return $horaire['fermeture'] < $horaire['ouverture'];
    }

    private static function ouvert(string $fermeture): array
    {
        return ['ouvert' => true, 'libelle' => "Ouvert jusqu'à ".self::formaterHeure($fermeture)];
    }

    private static function fermeJusqua(string $quand, string $heure): array
    {
        return ['ouvert' => false, 'libelle' => "Fermé — ouvre {$quand} à ".self::formaterHeure($heure)];
    }

    private static function formaterHeure(string $heure): string
    {
        [$h, $m] = array_pad(explode(':', $heure), 2, '00');

        return $m === '00' ? ((int) $h).'h' : ((int) $h).'h'.$m;
    }
}
