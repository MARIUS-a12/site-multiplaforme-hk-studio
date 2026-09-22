<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use Illuminate\Database\Seeder;

class EtablissementsDemoSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $chezAwa = Etablissement::create([
            'nom' => 'Chez Awa',
            'slug' => 'chez-awa',
            'type' => 'boutique',
        ]);

        $chezAwa->domaines()->create([
            'hote' => 'chez-awa.localhost',
            'est_principal' => true,
        ]);

        $maquisDuPort = Etablissement::create([
            'nom' => 'Maquis du Port',
            'slug' => 'maquis-du-port',
            'type' => 'restaurant',
        ]);

        $maquisDuPort->domaines()->create([
            'hote' => 'maquis-du-port.localhost',
            'est_principal' => true,
        ]);
    }
}
