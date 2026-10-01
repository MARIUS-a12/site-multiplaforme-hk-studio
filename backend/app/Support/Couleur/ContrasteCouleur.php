<?php

namespace App\Support\Couleur;

/**
 * Contraste d'une couleur d'accent contre du texte blanc (voir
 * MettreAJourIdentiteEtablissement) : la vitrine pose le texte du bouton
 * principal en blanc sur cette couleur (voir index.css côté frontend), donc
 * c'est CE contraste précis qui doit être suffisant — pas, comme pour une
 * couleur choisie librement n'importe où, "noir ou blanc, le meilleur des
 * deux" (voir lib/couleurAccent.ts côté frontend, qui répond à un besoin
 * différent).
 */
final class ContrasteCouleur
{
    private const SEUIL_MINIMUM = 4.5;

    public static function estSuffisantAvecBlanc(string $hex): bool
    {
        return self::ratioAvecBlanc($hex) >= self::SEUIL_MINIMUM;
    }

    public static function ratioAvecBlanc(string $hex): float
    {
        return 1.05 / (self::luminanceRelative($hex) + 0.05);
    }

    /**
     * Assombrit la couleur par paliers (chaque canal RGB multiplié par 0,92,
     * ce qui préserve la teinte) jusqu'à atteindre le seuil, ou jusqu'à
     * s'approcher du noir — la variante proposée reste reconnaissable, ce
     * n'est jamais un simple noir générique.
     */
    public static function assombrirJusquau(string $hex, float $ratioMinimum = self::SEUIL_MINIMUM): string
    {
        [$r, $g, $b] = self::versRgb($hex);

        for ($i = 0; $i < 60; $i++) {
            if (self::ratioAvecBlanc(self::versHex($r, $g, $b)) >= $ratioMinimum) {
                break;
            }

            $r = (int) floor($r * 0.92);
            $g = (int) floor($g * 0.92);
            $b = (int) floor($b * 0.92);
        }

        return self::versHex($r, $g, $b);
    }

    private static function luminanceRelative(string $hex): float
    {
        [$r, $g, $b] = self::versRgb($hex);

        $lineariser = function (int $canal): float {
            $valeur = $canal / 255;

            return $valeur <= 0.03928 ? $valeur / 12.92 : (($valeur + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $lineariser($r) + 0.7152 * $lineariser($g) + 0.0722 * $lineariser($b);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function versRgb(string $hex): array
    {
        $valeur = ltrim($hex, '#');

        return [
            hexdec(substr($valeur, 0, 2)),
            hexdec(substr($valeur, 2, 2)),
            hexdec(substr($valeur, 4, 2)),
        ];
    }

    private static function versHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }
}
