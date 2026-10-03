<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Correctif Étape 10 — activation par code : l'employé choisit lui-même son
 * mot de passe à la première connexion, personne d'autre ne le connaît
 * jamais (voir GenererCodeActivation / ActiverCompte). "code" est haché,
 * jamais stocké en clair — même garantie que "password" sur "users".
 * "expire_le" porte l'invalidation (générer un nouveau code pour le même
 * utilisateur expire l'ancien immédiatement plutôt que de le supprimer :
 * l'historique reste lisible).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codes_activation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            $table->string('code');
            $table->timestamp('expire_le');
            $table->timestamp('utilise_le')->nullable();
            $table->foreignId('cree_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('utilisateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codes_activation');
    }
};
