<?php

namespace Tests\Feature\Postgres\Commande;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Services\Stock\ConsommerReservation;
use App\Support\Tenancy\ContexteEtablissement;
use Tests\PostgresTestCase;

/**
 * Au paiement confirmé : quantite_stock baisse, quantite_reservee revient à
 * sa valeur d'avant réservation. Rejoue le scénario déjà couvert en SQLite
 * (Temps B) sur un vrai moteur PostgreSQL — en particulier les CHECK
 * (quantite_reservee <= quantite_stock, quantite_stock >= 0) que SQLite ne
 * vérifie pas : si la double décrémentation de ConsommerReservation était
 * dans le mauvais ordre ou rejouée, c'est ici qu'une contrainte le trahirait.
 */
class ConsommationTest extends PostgresTestCase
{
    public function test_le_paiement_decremente_le_stock_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement,
            $client,
            [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web,
            SourceCommande::PanierWeb,
            'cle-consommation-pg',
        );
        $this->assertSame(4, $produit->fresh()->quantite_reservee);

        $consommer = app(ConsommerReservation::class);
        $consommer->executer($commande);

        $produit->refresh();
        $this->assertSame(6, $produit->quantite_stock);
        $this->assertSame(0, $produit->quantite_reservee);
        $this->assertSame(StatutCommande::Payee, $commande->fresh()->statut);

        // Rejouer (webhook de paiement dupliqué) ne décrémente pas deux fois
        // — si c'était le cas, quantite_stock passerait à 2 puis les CHECK
        // (quantite_stock >= 0, quantite_reservee <= quantite_stock)
        // finiraient par le refuser sur un stock plus faible.
        $consommer->executer($commande);
        $produit->refresh();
        $this->assertSame(6, $produit->quantite_stock);
        $this->assertSame(0, $produit->quantite_reservee);
    }
}
