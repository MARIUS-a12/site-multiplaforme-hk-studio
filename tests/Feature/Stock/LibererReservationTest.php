<?php

namespace Tests\Feature\Stock;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutReservation;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Services\Stock\LibererReservation;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibererReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_liberer_rend_le_stock_de_la_variante_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-liberer-variante',
        );
        $reservation = $commande->reservations()->first();
        $this->assertSame(4, $variante->fresh()->quantite_reservee);

        $liberer = app(LibererReservation::class);
        $liberer->executer($reservation);

        $this->assertSame(0, $variante->fresh()->quantite_reservee);
        $this->assertSame(StatutReservation::Liberee, $reservation->fresh()->statut);
        $this->assertNotNull($reservation->fresh()->liberee_le);

        $liberer->executer($reservation);
        $this->assertSame(0, $variante->fresh()->quantite_reservee, 'rejouer ne doit rien libérer de plus');
    }

    public function test_liberer_rend_le_stock_du_produit_sans_variante(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-liberer-produit',
        );
        $this->assertSame(4, $produit->fresh()->quantite_reservee);

        $liberer = app(LibererReservation::class);
        $liberer->executer($commande->reservations()->first());

        $this->assertSame(0, $produit->fresh()->quantite_reservee);
        $this->assertSame(10, $produit->fresh()->quantite_stock, 'libérer ne touche jamais quantite_stock');

        $liberer->executer($commande->reservations()->first());
        $this->assertSame(0, $produit->fresh()->quantite_reservee);
    }
}
