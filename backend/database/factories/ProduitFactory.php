<?php

namespace Database\Factories;

use App\Enums\StatutProduit;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Models\VarianteProduit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Produit>
 */
class ProduitFactory extends Factory
{
    protected $model = Produit::class;

    public function definition(): array
    {
        $nom = ucfirst(fake()->unique()->words(3, true));

        return [
            'etablissement_id' => Etablissement::factory(),
            'categorie_id' => null,
            'nom' => $nom,
            'slug' => Str::slug($nom),
            'description' => fake()->optional()->paragraph(),
            'reference' => null,
            'prix' => fake()->numberBetween(500, 50000),
            'prix_barre' => null,
            // mode_stock volontairement absent : déduit du type de
            // l'établissement par Produit::booted(), comme en production.
            'quantite_stock' => fake()->numberBetween(5, 100),
            'quantite_reservee' => 0,
            'disponible' => true,
            'statut' => StatutProduit::Brouillon->value,
            'publie_le' => null,
        ];
    }

    public function publie(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutProduit::Publie,
            'publie_le' => now(),
        ]);
    }

    public function brouillon(): static
    {
        return $this->state(fn (array $attributes) => [
            'statut' => StatutProduit::Brouillon,
            'publie_le' => null,
        ]);
    }

    /**
     * Rend l'article indisponible quel que soit le mode de stock qui sera
     * déduit ensuite : stock et réservations à zéro pour le mode compte,
     * disponible à false pour le mode interrupteur.
     */
    public function epuise(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantite_stock' => 0,
            'quantite_reservee' => 0,
            'disponible' => false,
        ]);
    }

    /**
     * Crée le produit puis lui attache des variantes rattachées au même
     * établissement. Passe par afterCreating() plutôt que par has(), pour
     * garantir explicitement que chaque variante hérite du etablissement_id
     * réel du produit plutôt que d'un établissement factice indépendant.
     */
    public function avecVariantes(int $nombre = 2): static
    {
        return $this->afterCreating(function (Produit $produit) use ($nombre): void {
            VarianteProduit::factory()
                ->count($nombre)
                ->pourProduit($produit)
                ->create();
        });
    }
}
