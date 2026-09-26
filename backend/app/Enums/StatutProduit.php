<?php

namespace App\Enums;

enum StatutProduit: string
{
    case Brouillon = 'brouillon';
    case Publie = 'publie';
    case Archive = 'archive';
}
