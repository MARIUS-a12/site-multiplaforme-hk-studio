<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 10 — le titre affiché sous le nom de la boutique dans la barre
 * latérale ("Espace Administrateur", "Espace Caisse"...) doit venir de la
 * base, jamais d'une liste codée en dur côté React : ce champ en est la
 * source. Nullable : un rôle sans libellé d'espace retombe sur un texte
 * générique côté frontend plutôt que d'empêcher sa création.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('libelle_espace')->nullable()->after('libelle');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('libelle_espace');
        });
    }
};
