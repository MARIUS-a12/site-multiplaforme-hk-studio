<?php

namespace App\Enums;

/**
 * "Payee" est le pivot entre les deux cycles de vie d'une commande : côté
 * paiement, c'est la confirmation (voir ConsommerReservation) ; côté
 * back-office (Étape 9), c'est l'équivalent exact de "Confirmée" dans le
 * langage du commerçant. "Prete" et "Livree" n'existent qu'après elle, et ne
 * déclenchent eux-mêmes aucun mouvement de stock.
 */
enum StatutCommande: string
{
    case AttentePaiement = 'attente_paiement';
    case Payee = 'payee';
    case Prete = 'prete';
    case Livree = 'livree';
    case Expiree = 'expiree';
    case Annulee = 'annulee';

    public function libelle(): string
    {
        return match ($this) {
            self::AttentePaiement => 'En attente',
            self::Payee => 'Confirmée',
            self::Prete => 'Prête',
            self::Livree => 'Livrée',
            self::Expiree => 'Expirée',
            self::Annulee => 'Annulée',
        };
    }
}
