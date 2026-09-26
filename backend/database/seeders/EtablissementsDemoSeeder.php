<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Domaine;
use App\Models\Etablissement;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class EtablissementsDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $chezAwa = Etablissement::factory()->boutique()->create([
            'nom' => 'Chez Awa',
            'slug' => 'chez-awa',
        ]);

        Domaine::factory()->for($chezAwa)->create([
            'hote' => 'chez-awa.localhost',
            'est_principal' => true,
        ]);

        $maquisDuPort = Etablissement::factory()->restaurant()->create([
            'nom' => 'Maquis du Port',
            'slug' => 'maquis-du-port',
        ]);

        Domaine::factory()->for($maquisDuPort)->create([
            'hote' => 'maquis-du-port.localhost',
            'est_principal' => true,
        ]);

        $this->peuplerBoutique($chezAwa);
        $this->peuplerRestaurant($maquisDuPort);
    }

    /**
     * mode_stock n'est jamais forcé ici : il est déduit du type de
     * l'établissement par Produit::booted(), exactement comme en
     * production. Tous les produits sont publiés pour que la démo montre un
     * catalogue navigable.
     */
    private function peuplerBoutique(Etablissement $chezAwa): void
    {
        [$categorieA, $categorieB] = Categorie::factory()->for($chezAwa)->count(2)->create();

        Produit::factory()->for($chezAwa)->publie()->create(['categorie_id' => $categorieA->id]);

        Produit::factory()->for($chezAwa)->publie()->create([
            'categorie_id' => $categorieB->id,
            'quantite_stock' => 1,
        ]);

        Produit::factory()->for($chezAwa)->publie()->avecVariantes(2)->create([
            'categorie_id' => $categorieA->id,
        ]);

        Produit::factory()->for($chezAwa)->publie()->create(['categorie_id' => $categorieB->id]);
    }

    private function peuplerRestaurant(Etablissement $maquisDuPort): void
    {
        [$categorieA, $categorieB] = Categorie::factory()->for($maquisDuPort)->count(2)->create();

        Produit::factory()->for($maquisDuPort)->publie()->create(['categorie_id' => $categorieA->id]);
        Produit::factory()->for($maquisDuPort)->publie()->create(['categorie_id' => $categorieB->id]);
        Produit::factory()->for($maquisDuPort)->publie()->create(['categorie_id' => $categorieA->id]);

        Produit::factory()->for($maquisDuPort)->publie()->create([
            'categorie_id' => $categorieB->id,
            'disponible' => false,
        ]);
    }
}
