<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Correctif Étape 10 — faux tant que l'employé n'a pas lui-même choisi son
 * mot de passe via /admin/activation : un compte dans cet état ne peut pas
 * se connecter, quel que soit le mot de passe présenté (voir
 * SessionController::store()). Défaut à FAUX pour les nouvelles lignes :
 * CreerMembreEquipe pose désormais un mot de passe inutilisable et laisse
 * ce drapeau à faux, c'est ActiverCompte seul qui le repasse à vrai.
 *
 * Les comptes qui existent déjà au moment de cette migration ont tous un
 * mot de passe utilisable dès leur création (administrateurs générés par
 * CreerEtablissement, comptes de démo) : les repasser à vrai ici évite de
 * verrouiller tout le monde derrière une activation qui n'a jamais existé
 * pour eux.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('mot_de_passe_defini')->default(false)->after('password');
        });

        DB::table('users')->update(['mot_de_passe_defini' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mot_de_passe_defini');
        });
    }
};
