<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cf. la migration de garde-fous sur produits/variantes_produit :
        // les CHECK ne sont posés que sur PostgreSQL (syntaxe ALTER TABLE
        // ADD CONSTRAINT non fiable ailleurs).
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE categories ADD CONSTRAINT categories_statut_valide CHECK (statut IN ('actif', 'inactif'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE categories DROP CONSTRAINT categories_statut_valide');
    }
};
