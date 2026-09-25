<?php

namespace App\Enums;

enum StatutReservation: string
{
    case Active = 'active';
    case Liberee = 'liberee';
    case Consommee = 'consommee';
}
