<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Étape 9 — le cycle de vie du BACK-OFFICE (préparation, remise au client)
 * est distinct du cycle de paiement déjà modélisé par StatutCommande : il
 * manquait "prete" et "livree" entre "payee" (= Confirmée, premier
 * déclenchement réel de ConsommerReservation) et la fin de vie de la
 * commande. Ajout additif de la contrainte CHECK Postgres (voir
 * 2026_09_25_090100_add_garde_fous_stock_et_commande_tables.php) — aucune
 * ligne existante n'est affectée.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_statut_valide');
        DB::statement("ALTER TABLE commandes ADD CONSTRAINT commandes_statut_valide CHECK (statut IN ('attente_paiement', 'payee', 'prete', 'livree', 'expiree', 'annulee'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_statut_valide');
        DB::statement("ALTER TABLE commandes ADD CONSTRAINT commandes_statut_valide CHECK (statut IN ('attente_paiement', 'payee', 'expiree', 'annulee'))");
    }
};
