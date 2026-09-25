<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Séquence dédiée au numéro de commande (voir GenererNumeroCommande) :
     * un objet PostgreSQL natif, sans équivalent sur SQLite — même logique
     * que les CHECK de add_garde_fous_stock_et_commande_tables, gardées
     * séparées ici car ce n'est pas une contrainte mais un générateur de
     * valeur.
     *
     * IF NOT EXISTS, et non un DROP préalable : une séquence autonome
     * n'appartient à aucune table, donc `migrate:fresh` (qui ne fait que
     * supprimer les tables) la laisse en place et rejouerait sinon un
     * CREATE en erreur. Surtout, la recréer remettrait le compteur à 1 sur
     * une base qui a déjà des commandes — et ferait collisionner les numéros
     * avec l'unicité (etablissement_id, numero).
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE SEQUENCE IF NOT EXISTS commandes_numero_seq AS integer START WITH 1');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP SEQUENCE IF EXISTS commandes_numero_seq');
    }
};
