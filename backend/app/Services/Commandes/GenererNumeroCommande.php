<?php

namespace App\Services\Commandes;

use Illuminate\Support\Facades\DB;

/**
 * Un numéro dicté au téléphone ou tapé sur WhatsApp : court, humain,
 * `CMD-000847`. Généré par une séquence PostgreSQL (`nextval`) — contrairement
 * à un MAX(numero)+1, elle ne pose aucun verrou et ne fait donc jamais
 * attendre deux commandes concurrentes l'une sur l'autre, quels que soient
 * les produits qu'elles contiennent.
 *
 * Une séquence est un objet PostgreSQL natif, sans équivalent sur SQLite
 * (utilisé par la suite de tests rapide) : voir le même choix déjà fait pour
 * les contraintes CHECK dans add_garde_fous_stock_et_commande_tables. Le
 * repli ci-dessous n'a donc besoin d'aucune garantie de concurrence — cette
 * suite ne teste jamais plusieurs commandes en parallèle.
 */
class GenererNumeroCommande
{
    public function executer(): string
    {
        return sprintf('CMD-%06d', $this->prochaineValeur());
    }

    private function prochaineValeur(): int
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            return (int) DB::selectOne("select nextval('commandes_numero_seq') as valeur")->valeur;
        }

        return (int) (DB::table('commandes')->max('id') ?? 0) + 1;
    }
}
