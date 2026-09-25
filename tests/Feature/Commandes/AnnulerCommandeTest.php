<?php

namespace Tests\Feature\Commandes;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\AnnulerCommande;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Services\Stock\ConsommerReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnulerCommandeTest extends TestCase
{
    use RefreshDatabase;

    public function test_annuler_libere_le_stock_journalise_le_motif_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-annulation',
        );
        $this->assertSame(4, $produit->fresh()->quantite_reservee);

        $annuler = app(AnnulerCommande::class);
        $annuler->executer($commande, 'Client injoignable');

        $this->assertSame(StatutCommande::Annulee, $commande->fresh()->statut);
        $this->assertSame(0, $produit->fresh()->quantite_reservee);
        $this->assertSame(10, $produit->fresh()->quantite_stock);
        $this->assertSame(StatutReservation::Liberee, $commande->reservations()->first()->statut);

        $this->assertSame(2, $commande->historique()->count());
        $dernier = $commande->historique()->latest('id')->first();
        $this->assertSame(StatutCommande::AttentePaiement, $dernier->ancien_statut);
        $this->assertSame(StatutCommande::Annulee, $dernier->nouveau_statut);
        $this->assertSame('Client injoignable', $dernier->motif);

        $annuler->executer($commande, 'Deuxième tentative');
        $this->assertSame(0, $produit->fresh()->quantite_reservee);
        $this->assertSame(2, $commande->historique()->count(), 'rejouer ne journalise pas deux fois');
    }

    public function test_une_commande_deja_payee_ne_peut_plus_etre_annulee(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-payee-puis-annulee',
        );

        app(ConsommerReservation::class)->executer($commande);
        app(AnnulerCommande::class)->executer($commande, 'Trop tard');

        $this->assertSame(StatutCommande::Payee, $commande->fresh()->statut);
        $this->assertSame(6, $produit->fresh()->quantite_stock, 'le stock consommé ne doit pas revenir');
        $this->assertSame(2, $commande->historique()->count());
    }
}
