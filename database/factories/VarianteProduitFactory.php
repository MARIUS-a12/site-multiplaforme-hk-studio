<?php

namespace Database\Factories;

use App\Models\Etablissement;
use App\Models\Produit;
use App\Models\VarianteProduit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VarianteProduit>
 */
class VarianteProduitFactory extends Factory
{
    protected $model = VarianteProduit::class;

    public function definition(): array
    {
        return [
            'etablissement_id' => Etablissement::factory(),
            'produit_id' => Produit::factory(),
            'nom' => fake()->randomElement(['S', 'M', 'L', 'XL']).'-'.fake()->unique()->numberBetween(1, 999999),
            'reference' => null,
            'prix' => null,
            'quantite_stock' => fake()->numberBetween(1, 50),
            'quantite_reservee' => 0,
            'disponible' => true,
            'attributs_json' => null,
            'statut' => 'actif',
        ];
    }

    /**
     * Rattache la variante à un produit réel, en reprenant explicitement
     * son etablissement_id : le simple `for($produit)` ne fixerait que
     * produit_id (relation belongsTo), pas etablissement_id, qui n'a pas de
     * lien de relation avec `produit`.
     */
    public function pourProduit(Produit $produit): static
    {
        return $this->state(fn (array $attributes) => [
            'produit_id' => $produit->id,
            'etablissement_id' => $produit->etablissement_id,
        ]);
    }
}
