<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Étape 10 — deux changements sur "users" pour la gestion d'équipe :
 *
 * 1. L'email n'est plus unique GLOBALEMENT : le même email peut désormais
 *    exister pour deux comptes distincts dans deux établissements
 *    différents (voir CreerMembreEquipe, qui crée toujours une ligne User à
 *    part, jamais partagée). L'unicité qui compte reste "au sein d'un même
 *    établissement", vérifiée par StoreMembreEquipeRequest — SessionController
 *    résout désormais la bonne ligne en scopant sa recherche par
 *    l'appartenance à l'établissement du sous-domaine courant plutôt que par
 *    un simple where('email', ...) global, qui serait sinon ambigu.
 * 2. "dernière connexion" (demandée dans la liste de l'équipe) n'existait
 *    nulle part : ajoutée ici, posée à chaque connexion réussie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
            $table->timestamp('derniere_connexion_a')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('derniere_connexion_a');
            $table->dropIndex(['email']);
            $table->unique('email');
        });
    }
};
