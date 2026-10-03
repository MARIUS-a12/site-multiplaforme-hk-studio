<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correctif livraison — remplace le champ Email et le champ Note du
 * formulaire /commander par deux informations dont un livreur a réellement
 * besoin : la commune (qui détermine le frais via la zone de livraison
 * correspondante, voir CreerCommandeVitrineRequest) et le quartier (texte
 * libre, jamais relié à une zone). Nullables : les commandes déjà
 * enregistrées n'ont ni l'une ni l'autre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->string('commune', 150)->nullable();
            $table->string('quartier', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn(['commune', 'quartier']);
        });
    }
};
