<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garde-fou DEMANDÉ en base, en plus de celui déjà posé par
 * StoreMembreEquipeRequest côté application : deux comptes du même
 * établissement ne peuvent pas partager un email, même par un chemin qui
 * contournerait la validation (écriture directe, future route, etc.).
 *
 * "etablissement_id" n'existait PAS sur "users" avant cette migration — le
 * rattachement passe par la table pivot "etablissement_utilisateurs" (voir
 * EtablissementUtilisateur), un utilisateur pouvant structurellement avoir
 * plusieurs appartenances. En pratique, CreerEtablissement et
 * CreerMembreEquipe créent TOUJOURS une ligne User dédiée à un seul
 * établissement (jamais partagée, voir leur docblock) : chaque utilisateur
 * n'a donc aujourd'hui qu'une seule appartenance, ce qui rend cette colonne
 * dénormalisée fiable. Nullable : le super-admin n'a aucun établissement.
 *
 * Si un utilisateur venait un jour à avoir plusieurs appartenances, cette
 * colonne (et la contrainte qui s'appuie sur elle) cesserait de représenter
 * fidèlement "tous les établissements de cet utilisateur" — elle resterait
 * correcte pour le cas que l'application produit réellement aujourd'hui,
 * mais plus un garde-fou complet si ce cas changeait.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('etablissement_id')->nullable()->after('id')
                ->references('id')->on('etablissements')->nullOnDelete();
        });

        // Chaque utilisateur n'a aujourd'hui qu'une seule appartenance (voir
        // la docblock ci-dessus) : LIMIT 1 n'est qu'une sécurité, jamais une
        // ambiguïté réelle au moment où cette migration tourne.
        DB::statement(<<<'SQL'
            UPDATE users
            SET etablissement_id = (
                SELECT eu.etablissement_id
                FROM etablissement_utilisateurs eu
                WHERE eu.utilisateur_id = users.id
                LIMIT 1
            )
            WHERE EXISTS (
                SELECT 1 FROM etablissement_utilisateurs eu WHERE eu.utilisateur_id = users.id
            )
        SQL);

        $doublons = DB::table('users')
            ->select('etablissement_id', 'email')
            ->whereNotNull('etablissement_id')
            ->groupBy('etablissement_id', 'email')
            ->havingRaw('count(*) > 1')
            ->get();

        if ($doublons->isNotEmpty()) {
            throw new RuntimeException(
                "Impossible d'ajouter la contrainte d'unicité : des doublons existent déjà pour ".
                $doublons->map(fn ($d) => "établissement {$d->etablissement_id} / {$d->email}")->implode(', '),
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['etablissement_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['etablissement_id', 'email']);
            $table->dropColumn('etablissement_id');
        });
    }
};
