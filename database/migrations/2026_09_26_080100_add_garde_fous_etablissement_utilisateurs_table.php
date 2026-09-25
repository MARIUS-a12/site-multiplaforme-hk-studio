<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Les CHECK ne sont pas exprimables via le Schema Builder de Laravel :
        // voir 2026_09_22_190100_add_garde_fous_produits_et_variantes_produit_table.php.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE etablissement_utilisateurs ADD CONSTRAINT etablissement_utilisateurs_statut_valide CHECK (statut IN ('actif', 'suspendu'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE etablissement_utilisateurs DROP CONSTRAINT etablissement_utilisateurs_statut_valide');
    }
};
