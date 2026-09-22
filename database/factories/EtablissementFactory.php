<?php

namespace Database\Factories;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Etablissement>
 */
class EtablissementFactory extends Factory
{
    protected $model = Etablissement::class;

    public function definition(): array
    {
        $nom = fake()->unique()->company();

        return [
            'nom' => $nom,
            'slug' => Str::slug($nom).'-'.fake()->unique()->numberBetween(1, 999999),
            'type' => fake()->randomElement(['boutique', 'restaurant']),
            'raison_sociale' => null,
            'email' => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'statut' => 'actif',
            'fuseau_horaire' => 'Africa/Abidjan',
            'devise' => 'XOF',
        ];
    }

    public function boutique(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'boutique']);
    }

    public function restaurant(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'restaurant']);
    }
}
