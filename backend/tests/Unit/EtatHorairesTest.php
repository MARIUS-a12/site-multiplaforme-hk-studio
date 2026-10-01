<?php

namespace Tests\Unit;

use App\Support\Horaires\EtatHoraires;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Étape 6A ter, test n°11 : l'horaire qui traverse minuit (samedi 20:00 ->
 * 02:00) et le jour fermé doivent tous deux être jugés correctement. Le 4
 * janvier 2025 est un samedi (vérifié) : le 5 janvier est donc un dimanche,
 * le 6 un lundi.
 */
class EtatHorairesTest extends TestCase
{
    private const HORAIRES = [
        'lundi' => ['ouverture' => '08:00', 'fermeture' => '19:00', 'ferme' => false],
        'mardi' => ['ouverture' => '08:00', 'fermeture' => '19:00', 'ferme' => false],
        'mercredi' => ['ouverture' => '08:00', 'fermeture' => '19:00', 'ferme' => false],
        'jeudi' => ['ouverture' => '08:00', 'fermeture' => '19:00', 'ferme' => false],
        'vendredi' => ['ouverture' => '08:00', 'fermeture' => '19:00', 'ferme' => false],
        // Traverse minuit : ferme à 2h du matin, le lendemain.
        'samedi' => ['ouverture' => '20:00', 'fermeture' => '02:00', 'ferme' => false],
        'dimanche' => ['ouverture' => null, 'fermeture' => null, 'ferme' => true],
    ];

    public function test_horaire_traversant_minuit_est_encore_ouvert_tot_le_dimanche_matin(): void
    {
        $dimancheUneHeure = CarbonImmutable::create(2025, 1, 5, 1, 0, 0, 'Africa/Abidjan');
        $this->assertSame(7, $dimancheUneHeure->dayOfWeekIso); // garde-fou : bien un dimanche

        $etat = EtatHoraires::calculer(self::HORAIRES, $dimancheUneHeure);

        $this->assertTrue($etat['ouvert']);
        $this->assertSame("Ouvert jusqu'à 2h", $etat['libelle']);
    }

    public function test_horaire_traversant_minuit_est_ferme_apres_sa_fermeture(): void
    {
        $dimancheTroisHeures = CarbonImmutable::create(2025, 1, 5, 3, 0, 0, 'Africa/Abidjan');

        $etat = EtatHoraires::calculer(self::HORAIRES, $dimancheTroisHeures);

        $this->assertFalse($etat['ouvert']);
    }

    public function test_jour_ferme_annonce_la_prochaine_ouverture_le_lendemain(): void
    {
        $dimancheMatin = CarbonImmutable::create(2025, 1, 5, 10, 0, 0, 'Africa/Abidjan');

        $etat = EtatHoraires::calculer(self::HORAIRES, $dimancheMatin);

        $this->assertFalse($etat['ouvert']);
        $this->assertSame('Fermé — ouvre demain à 8h', $etat['libelle']);
    }

    public function test_pendant_les_heures_normales_du_jour_meme(): void
    {
        $lundiMidi = CarbonImmutable::create(2025, 1, 6, 12, 0, 0, 'Africa/Abidjan');

        $etat = EtatHoraires::calculer(self::HORAIRES, $lundiMidi);

        $this->assertTrue($etat['ouvert']);
        $this->assertSame("Ouvert jusqu'à 19h", $etat['libelle']);
    }

    public function test_horaires_non_renseignes_renvoie_null(): void
    {
        $this->assertNull(EtatHoraires::calculer(null));
    }
}
