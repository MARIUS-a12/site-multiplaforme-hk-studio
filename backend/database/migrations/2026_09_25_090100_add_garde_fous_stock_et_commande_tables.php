<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Les CHECK ne sont pas exprimables via le Schema Builder de Laravel :
        // voir 2026_09_22_190100_add_garde_fous_produits_et_variantes_produit_table.php.
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE zones_livraison ADD CONSTRAINT zones_livraison_frais_positif CHECK (frais >= 0)');
        DB::statement("ALTER TABLE zones_livraison ADD CONSTRAINT zones_livraison_statut_valide CHECK (statut IN ('actif', 'inactif'))");

        DB::statement('ALTER TABLE commandes ADD CONSTRAINT commandes_sous_total_positif CHECK (sous_total >= 0)');
        DB::statement('ALTER TABLE commandes ADD CONSTRAINT commandes_remise_positive CHECK (remise >= 0)');
        DB::statement('ALTER TABLE commandes ADD CONSTRAINT commandes_frais_livraison_positif CHECK (frais_livraison >= 0)');
        DB::statement('ALTER TABLE commandes ADD CONSTRAINT commandes_total_positif CHECK (total >= 0)');
        DB::statement("ALTER TABLE commandes ADD CONSTRAINT commandes_statut_valide CHECK (statut IN ('attente_paiement', 'payee', 'expiree', 'annulee'))");
        DB::statement("ALTER TABLE commandes ADD CONSTRAINT commandes_source_valide CHECK (source IN ('panier_web', 'achat_express', 'bouton_whatsapp', 'ia_whatsapp', 'back_office'))");
        DB::statement("ALTER TABLE commandes ADD CONSTRAINT commandes_canal_valide CHECK (canal IN ('web', 'whatsapp'))");

        DB::statement('ALTER TABLE lignes_commande ADD CONSTRAINT lignes_commande_quantite_positive CHECK (quantite > 0)');
        DB::statement('ALTER TABLE lignes_commande ADD CONSTRAINT lignes_commande_prix_unitaire_positif CHECK (prix_unitaire >= 0)');
        DB::statement('ALTER TABLE lignes_commande ADD CONSTRAINT lignes_commande_total_positif CHECK (total >= 0)');

        DB::statement('ALTER TABLE reservations_stock ADD CONSTRAINT reservations_stock_quantite_positive CHECK (quantite > 0)');
        DB::statement("ALTER TABLE reservations_stock ADD CONSTRAINT reservations_stock_statut_valide CHECK (statut IN ('active', 'liberee', 'consommee'))");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE reservations_stock DROP CONSTRAINT reservations_stock_statut_valide');
        DB::statement('ALTER TABLE reservations_stock DROP CONSTRAINT reservations_stock_quantite_positive');

        DB::statement('ALTER TABLE lignes_commande DROP CONSTRAINT lignes_commande_total_positif');
        DB::statement('ALTER TABLE lignes_commande DROP CONSTRAINT lignes_commande_prix_unitaire_positif');
        DB::statement('ALTER TABLE lignes_commande DROP CONSTRAINT lignes_commande_quantite_positive');

        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_canal_valide');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_source_valide');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_statut_valide');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_total_positif');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_frais_livraison_positif');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_remise_positive');
        DB::statement('ALTER TABLE commandes DROP CONSTRAINT commandes_sous_total_positif');

        DB::statement('ALTER TABLE zones_livraison DROP CONSTRAINT zones_livraison_statut_valide');
        DB::statement('ALTER TABLE zones_livraison DROP CONSTRAINT zones_livraison_frais_positif');
    }
};
