<?php

namespace Database\Factories;

use App\Models\Domaine;
use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domaine>
 */
class DomaineFactory extends Factory
{
    protected $model = Domaine::class;

    public function definition(): array
    {
        return [
            'etablissement_id' => Etablissement::factory(),
            'hote' => fake()->unique()->domainWord().'.localhost',
            'type' => 'sous_domaine',
            'est_principal' => true,
            'verifie_le' => null,
            'statut' => 'actif',
        ];
    }
}
