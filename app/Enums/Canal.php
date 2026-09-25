<?php

namespace App\Enums;

/**
 * Canal de communication utilisé pour la commande. Détermine notamment le
 * délai avant expiration des réservations de stock (voir CreerCommande).
 */
enum Canal: string
{
    case Web = 'web';
    case Whatsapp = 'whatsapp';
}
