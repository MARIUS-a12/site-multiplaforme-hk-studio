<?php

namespace App\Http\Resources\Vitrine\Concerns;

use App\Enums\StatutVariante;
use App\Models\Produit;

/**
 * Partagé entre ProduitVitrineResource (liste) et ProduitVitrineDetailResource
 * (fiche) : les deux exposent exactement la même forme de variante, jamais
 * de quantité — seulement prix et disponibilité.
 *
 * Calcule la disponibilité inline (mode_stock du produit déjà en mémoire,
 * colonnes de la variante déjà chargées) plutôt que via
 * VarianteProduit::estDisponibleEnQuantite(), qui relancerait une requête
 * pour recharger CE MÊME produit depuis chaque variante — voir la docblock
 * de Produit::estDisponibleEnQuantite() pour ce même piège déjà documenté.
 */
trait ExposeVariantesPubliques
{
    /**
     * @return array<int, array{id: int, nom: string, prix: int, disponible: bool}>
     */
    protected function variantesPubliques(Produit $produit): array
    {
        return $produit->variantes
            ->filter(fn ($variante) => $variante->statut === StatutVariante::Actif)
            ->map(fn ($variante) => [
                'id' => $variante->id,
                'nom' => $variante->nom,
                'prix' => $variante->prixEffectif(),
                'disponible' => $produit->mode_stock->estDisponibleEnQuantite(
                    $variante->quantite_stock,
                    $variante->quantite_reservee,
                    $variante->disponible,
                    1,
                ),
            ])
            ->values()
            ->all();
    }
}
