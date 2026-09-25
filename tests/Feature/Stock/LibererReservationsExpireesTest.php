<?php

namespace Tests\Feature\Stock;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Jobs\LibererReservationsExpirees;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Services\Stock\LibererReservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibererReservationsExpireesTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_job_libere_le_stock_expire_expire_la_commande_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-expiration',
        );
        $this->assertSame(4, $variante->fresh()->quantite_reservee);

        // Le délai de paiement est dépassé.
        $commande->forceFill(['expire_le' => now()->subMinute()])->save();
        $commande->reservations()->update(['expire_le' => now()->subMinute()]);

        $this->executerLeJob();

        $this->assertSame(0, $variante->fresh()->quantite_reservee);
        $this->assertSame(10, $variante->fresh()->quantite_stock);
        $this->assertSame(StatutCommande::Expiree, $commande->fresh()->statut);
        $this->assertSame(StatutReservation::Liberee, $commande->reservations()->first()->statut);

        // Historique : création puis expiration, avec un motif.
        $this->assertSame(2, $commande->historique()->count());
        $dernier = $commande->historique()->latest('id')->first();
        $this->assertSame(StatutCommande::AttentePaiement, $dernier->ancien_statut);
        $this->assertSame(StatutCommande::Expiree, $dernier->nouveau_statut);
        $this->assertNotNull($dernier->motif);

        $this->executerLeJob();

        $this->assertSame(0, $variante->fresh()->quantite_reservee, 'rejouer ne crédite pas deux fois');
        $this->assertSame(2, $commande->historique()->count(), 'rejouer ne journalise pas deux fois');
    }

    public function test_le_job_ne_touche_pas_une_commande_encore_dans_les_delais(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 3)],
            Canal::Whatsapp, SourceCommande::IaWhatsapp, 'cle-encore-valide',
        );

        $this->executerLeJob();

        $this->assertSame(3, $produit->fresh()->quantite_reservee);
        $this->assertSame(StatutCommande::AttentePaiement, $commande->fresh()->statut);
        $this->assertSame(StatutReservation::Active, $commande->reservations()->first()->statut);
    }

    private function executerLeJob(): void
    {
        app(LibererReservationsExpirees::class)->handle(app(LibererReservation::class));
    }
}
