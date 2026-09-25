<?php

namespace App\Services\Commandes;

use App\Models\Produit;
use App\Models\VarianteProduit;

/**
 * Une ligne de CreerCommande une fois le produit et, le cas échéant, la
 * variante chargés et validés — tout ce qu'il faut pour vérifier la
 * disponibilité, réserver, et capturer les valeurs figées de la ligne.
 */
final readonly class LigneResolue
{
    public function __construct(
        public Produit $produit,
        public ?VarianteProduit $variante,
        public int $quantite,
    ) {}

    public function prixUnitaire(): int
    {
        return $this->variante?->prixEffectif() ?? $this->produit->prix;
    }

    public function total(): int
    {
        return $this->prixUnitaire() * $this->quantite;
    }

    public function nomCapture(): string
    {
        return $this->variante !== null
            ? "{$this->produit->nom} — {$this->variante->nom}"
            : $this->produit->nom;
    }

    public function referenceCapture(): ?string
    {
        return $this->variante?->reference ?? $this->produit->reference;
    }
}
