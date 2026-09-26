<?php

namespace App\Enums;

enum StatutCommande: string
{
    case AttentePaiement = 'attente_paiement';
    case Payee = 'payee';
    case Expiree = 'expiree';
    case Annulee = 'annulee';
}
