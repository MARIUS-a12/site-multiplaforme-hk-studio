<?php

namespace App\Enums;

enum ModeStock: string
{
    case Compte = 'compte';
    case Interrupteur = 'interrupteur';

    /**
     * Applique la règle de disponibilité propre à ce mode à un jeu de
     * colonnes de stock (celles du produit, ou celles d'une variante).
     */
    public function estDisponibleEnQuantite(
        int $quantiteStock,
        int $quantiteReservee,
        bool $disponible,
        int $quantiteDemandee,
    ): bool {
        return match ($this) {
            self::Compte => ($quantiteStock - $quantiteReservee) >= $quantiteDemandee,
            self::Interrupteur => $disponible,
        };
    }
}
