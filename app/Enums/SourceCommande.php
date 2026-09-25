<?php

namespace App\Enums;

/**
 * Qui a initié la commande, distinct du canal. Un même canal (whatsapp) peut
 * être utilisé aussi bien par le client lui-même que par l'IA agissant en
 * son nom. Sert de métrique commerciale : quel bouton d'achat convertit le
 * plus.
 */
enum SourceCommande: string
{
    case PanierWeb = 'panier_web';
    case AchatExpress = 'achat_express';
    case BoutonWhatsapp = 'bouton_whatsapp';
    case IaWhatsapp = 'ia_whatsapp';
    case BackOffice = 'back_office';
}
