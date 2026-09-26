<?php

namespace Database\Factories;

use App\Models\Etablissement;
use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $nomFichier = fake()->unique()->uuid().'.jpg';

        return [
            'etablissement_id' => Etablissement::factory(),
            'disque' => 'public',
            'chemin' => 'medias/'.$nomFichier,
            'url' => 'https://example.test/storage/medias/'.$nomFichier,
            'type_mime' => 'image/jpeg',
            'taille' => fake()->numberBetween(10_000, 2_000_000),
            'metadonnees_json' => null,
        ];
    }
}
