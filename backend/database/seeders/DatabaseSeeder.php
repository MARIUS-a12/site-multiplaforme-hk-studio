<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesEtPermissionsSeeder::class);
        $this->call(EtablissementsDemoSeeder::class);
        $this->call(UtilisateursDemoSeeder::class);
    }
}
