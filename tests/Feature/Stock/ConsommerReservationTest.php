<?php

namespace Tests\Feature\Stock;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Services\Stock\ConsommerReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsommerReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_consommer_decremente_le_stock_de_la_variante_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-consommer-variante',
        );

        $consommer = app(ConsommerReservation::class);
        $consommer->executer($commande);

        $variante->refresh();
        $this->assertSame(6, $variante->quantite_stock);
        $this->assertSame(0, $variante->quantite_reservee);
        $this->assertSame(StatutCommande::Payee, $commande->fresh()->statut);
        $this->assertNotNull($commande->fresh()->payee_le);
        $this->assertSame(StatutReservation::Consommee, $commande->reservations()->first()->statut);

        // Historique : création puis paiement.
        $this->assertSame(2, $commande->historique()->count());
        $dernier = $commande->historique()->latest('id')->first();
        $this->assertSame(StatutCommande::AttentePaiement, $dernier->ancien_statut);
        $this->assertSame(StatutCommande::Payee, $dernier->nouveau_statut);

        // Webhook de paiement dupliqué : aucun effet supplémentaire.
        $consommer->executer($commande);
        $variante->refresh();
        $this->assertSame(6, $variante->quantite_stock);
        $this->assertSame(0, $variante->quantite_reservee);
        $this->assertSame(2, $commande->historique()->count(), 'rejouer ne journalise pas deux fois');
    }

    public function test_consommer_decremente_le_stock_du_produit_sans_variante(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-consommer-produit',
        );

        $consommer = app(ConsommerReservation::class);
        $consommer->executer($commande);

        $produit->refresh();
        $this->assertSame(6, $produit->quantite_stock);
        $this->assertSame(0, $produit->quantite_reservee);

        $consommer->executer($commande);
        $produit->refresh();
        $this->assertSame(6, $produit->quantite_stock);
        $this->assertSame(0, $produit->quantite_reservee);
    }
}
