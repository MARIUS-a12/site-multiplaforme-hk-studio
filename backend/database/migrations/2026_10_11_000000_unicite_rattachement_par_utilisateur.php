<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un compte appartient à UN SEUL établissement (quelqu'un travaillant dans
 * deux boutiques a deux comptes distincts) : l'ancienne contrainte
 * UNIQUE(etablissement_id, utilisateur_id), posée par
 * create_etablissement_utilisateurs_table, n'empêchait qu'un DOUBLON du
 * même couple — pas un second rattachement du même utilisateur vers un
 * AUTRE établissement. Remplacée par une contrainte sur utilisateur_id
 * seul, strictement plus forte (elle implique l'ancienne).
 *
 * Le nom réel de l'ancien index diffère selon le moteur : Postgres tronque
 * à 63 octets les noms auto-générés ("..._uniq"), SQLite (utilisé par la
 * suite de tests par défaut) ne tronque pas ("..._unique") — reconstruire
 * ce nom à la main serait donc juste quant à UN SEUL moteur. On le relit
 * plutôt depuis le catalogue système de la connexion courante.
 */
return new class extends Migration
{
    public function up(): void
    {
        $nomAncienIndex = $this->nomIndexExistant();

        if ($nomAncienIndex !== null) {
            Schema::table('etablissement_utilisateurs', function (Blueprint $table) use ($nomAncienIndex) {
                $table->dropUnique($nomAncienIndex);
            });
        }

        Schema::table('etablissement_utilisateurs', function (Blueprint $table) {
            $table->unique('utilisateur_id');
        });
    }

    public function down(): void
    {
        Schema::table('etablissement_utilisateurs', function (Blueprint $table) {
            $table->dropUnique(['utilisateur_id']);
            $table->unique(['etablissement_id', 'utilisateur_id']);
        });
    }

    private function nomIndexExistant(): ?string
    {
        $pilote = Schema::getConnection()->getDriverName();

        if ($pilote === 'sqlite') {
            return DB::selectOne(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND tbl_name = 'etablissement_utilisateurs' AND name LIKE '%etablissement_id_utilisateur_id%'"
            )?->name;
        }

        return DB::selectOne(
            "SELECT indexname FROM pg_indexes WHERE tablename = 'etablissement_utilisateurs' AND indexname LIKE '%etablissement_id_utilisateur_id%'"
        )?->indexname;
    }
};
