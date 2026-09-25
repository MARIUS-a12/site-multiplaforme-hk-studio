<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etablissement_utilisateurs', function (Blueprint $table) {
            $table->id();
            // Le super-admin n'a AUCUNE ligne dans cette table : il n'appartient
            // à aucun établissement, ce que porte users.est_super_admin, pas
            // une ligne ici avec un etablissement_id à NULL.
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->foreignId('utilisateur_id')->constrained('users')->cascadeOnDelete();
            // restrictOnDelete, pas cascade : un rôle en cours d'utilisation
            // ne se supprime pas, il archive ses affectations d'abord.
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('statut')->default('actif');
            $table->timestamps();

            $table->unique(['etablissement_id', 'utilisateur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etablissement_utilisateurs');
    }
};
