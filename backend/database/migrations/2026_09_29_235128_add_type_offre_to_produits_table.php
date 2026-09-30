<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Point d'accrochage pour une future offre de type "service" (voir
 * App\Enums\TypeOffre) : tous les produits existants et tous les nouveaux
 * valent "bien" par défaut, rien ne change dans le comportement actuel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->string('type_offre')->default('bien')->after('categorie_id');
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->index('type_offre');
        });

        // Voir 2026_09_22_190100_add_garde_fous_produits_et_variantes_produit_table
        // pour l'explication de cette garde : les CHECK ne sont pas
        // exprimables via le Schema Builder, et cette syntaxe est propre à
        // PostgreSQL.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("ALTER TABLE produits ADD CONSTRAINT produits_type_offre_valide CHECK (type_offre IN ('bien', 'service'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_type_offre_valide');
        }

        Schema::table('produits', function (Blueprint $table) {
            $table->dropIndex(['type_offre']);
            $table->dropColumn('type_offre');
        });
    }
};
