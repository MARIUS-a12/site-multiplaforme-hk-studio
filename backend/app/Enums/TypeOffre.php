<?php

namespace App\Enums;

/**
 * Point d'accrochage pour une future offre de type "service" (salon,
 * garage, cabinet — du temps réservé, pas un bien stocké). Rien n'utilise
 * encore ce distingo : aucun agenda, aucun créneau, aucune interface. Seule
 * necessiteStock() existe déjà, pour le jour où la disponibilité d'un
 * service devra être calculée autrement que par un stock compté.
 */
enum TypeOffre: string
{
    case Bien = 'bien';
    case Service = 'service';

    public function necessiteStock(): bool
    {
        return $this === self::Bien;
    }
}
