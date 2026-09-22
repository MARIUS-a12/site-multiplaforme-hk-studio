<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->index(['etablissement_id', 'categorie_id']);
            $table->unique(['etablissement_id', 'reference']);
        });

        Schema::table('variantes_produit', function (Blueprint $table) {
            $table->index(['etablissement_id', 'produit_id']);
            $table->unique(['produit_id', 'nom']);
            $table->unique(['etablissement_id', 'reference']);
        });

        // Les CHECK ne sont pas exprimables via le Schema Builder de Laravel,
        // et la syntaxe ALTER COLUMN ... TYPE est propre à PostgreSQL : ce
        // bloc ne s'exécute donc que sur ce moteur. Sur SQLite (utilisé par
        // la suite de tests rapide), ces garanties ne sont pas posées au
        // niveau base ; elles devront être vérifiées par une suite dédiée
        // tournant sur une vraie base PostgreSQL.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE produits ADD CONSTRAINT produits_quantite_stock_positive CHECK (quantite_stock >= 0)');
        DB::statement('ALTER TABLE produits ADD CONSTRAINT produits_quantite_reservee_positive CHECK (quantite_reservee >= 0)');
        DB::statement('ALTER TABLE produits ADD CONSTRAINT produits_quantite_reservee_sous_stock CHECK (quantite_reservee <= quantite_stock)');
        DB::statement('ALTER TABLE produits ADD CONSTRAINT produits_prix_positif CHECK (prix >= 0 AND (prix_barre IS NULL OR prix_barre >= 0))');
        DB::statement("ALTER TABLE produits ADD CONSTRAINT produits_statut_valide CHECK (statut IN ('brouillon', 'publie', 'archive'))");
        DB::statement("ALTER TABLE produits ADD CONSTRAINT produits_mode_stock_valide CHECK (mode_stock IN ('compte', 'interrupteur'))");

        DB::statement('ALTER TABLE variantes_produit ADD CONSTRAINT variantes_produit_quantite_stock_positive CHECK (quantite_stock >= 0)');
        DB::statement('ALTER TABLE variantes_produit ADD CONSTRAINT variantes_produit_quantite_reservee_positive CHECK (quantite_reservee >= 0)');
        DB::statement('ALTER TABLE variantes_produit ADD CONSTRAINT variantes_produit_quantite_reservee_sous_stock CHECK (quantite_reservee <= quantite_stock)');
        DB::statement('ALTER TABLE variantes_produit ADD CONSTRAINT variantes_produit_prix_positif CHECK (prix IS NULL OR prix >= 0)');
        DB::statement("ALTER TABLE variantes_produit ADD CONSTRAINT variantes_produit_statut_valide CHECK (statut IN ('actif', 'inactif'))");

        DB::statement('ALTER TABLE variantes_produit ALTER COLUMN attributs_json TYPE jsonb USING attributs_json::jsonb');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE variantes_produit ALTER COLUMN attributs_json TYPE json USING attributs_json::json');

            DB::statement('ALTER TABLE variantes_produit DROP CONSTRAINT variantes_produit_statut_valide');
            DB::statement('ALTER TABLE variantes_produit DROP CONSTRAINT variantes_produit_prix_positif');
            DB::statement('ALTER TABLE variantes_produit DROP CONSTRAINT variantes_produit_quantite_reservee_sous_stock');
            DB::statement('ALTER TABLE variantes_produit DROP CONSTRAINT variantes_produit_quantite_reservee_positive');
            DB::statement('ALTER TABLE variantes_produit DROP CONSTRAINT variantes_produit_quantite_stock_positive');

            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_mode_stock_valide');
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_statut_valide');
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_prix_positif');
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_quantite_reservee_sous_stock');
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_quantite_reservee_positive');
            DB::statement('ALTER TABLE produits DROP CONSTRAINT produits_quantite_stock_positive');
        }

        Schema::table('variantes_produit', function (Blueprint $table) {
            $table->dropUnique(['etablissement_id', 'reference']);
            $table->dropUnique(['produit_id', 'nom']);
            $table->dropIndex(['etablissement_id', 'produit_id']);
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->dropUnique(['etablissement_id', 'reference']);
            $table->dropIndex(['etablissement_id', 'categorie_id']);
        });
    }
};
