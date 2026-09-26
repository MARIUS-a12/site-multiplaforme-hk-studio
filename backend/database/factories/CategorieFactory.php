<?php

namespace Database\Factories;

use App\Enums\StatutCategorie;
use App\Models\Categorie;
use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categorie>
 */
class CategorieFactory extends Factory
{
    protected $model = Categorie::class;

    public function definition(): array
    {
        $nom = ucfirst(fake()->unique()->words(2, true));

        return [
            'etablissement_id' => Etablissement::factory(),
            'nom' => $nom,
            'slug' => Str::slug($nom),
            'description' => fake()->optional()->sentence(),
            'ordre' => 0,
            'statut' => StatutCategorie::Actif->value,
        ];
    }
}
