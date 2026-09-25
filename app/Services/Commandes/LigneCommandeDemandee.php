<?php

namespace App\Services\Commandes;

/**
 * Une ligne telle que demandée par l'appelant (panier web, IA WhatsApp...),
 * avant toute résolution : `varianteId` n'est renseigné que si le client a
 * explicitement choisi une déclinaison.
 */
final readonly class LigneCommandeDemandee
{
    public function __construct(
        public int $produitId,
        public ?int $varianteId,
        public int $quantite,
    ) {}
}
