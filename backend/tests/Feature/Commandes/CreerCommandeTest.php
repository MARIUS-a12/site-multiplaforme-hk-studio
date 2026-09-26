<?php

namespace Tests\Feature\Commandes;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Exceptions\ArticleIndisponibleException;
use App\Exceptions\SelectionVarianteInvalideException;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreerCommandeTest extends TestCase
{
    use RefreshDatabase;

    public function test_mode_compte_avec_variante_reserve_et_est_idempotent(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        $service = app(CreerCommande::class);
        $lignes = [new LigneCommandeDemandee($produit->id, null, 3)];

        $commande = $service->executer($etablissement, $client, $lignes, Canal::Web, SourceCommande::PanierWeb, 'cle-variante');

        $this->assertSame(StatutCommande::AttentePaiement, $commande->statut);
        $this->assertMatchesRegularExpression('/^CMD-\d{6,}$/', $commande->numero);
        $this->assertSame(1, $commande->lignes()->count());
        $this->assertSame(1, $commande->reservations()->count());
        $this->assertSame($variante->id, $commande->reservations()->first()->variante_id);
        $this->assertSame(StatutReservation::Active, $commande->reservations()->first()->statut);
        $this->assertSame(3, $variante->fresh()->quantite_reservee);

        // Historique de la création, écrit dans la même transaction.
        $this->assertSame(1, $commande->historique()->count());
        $historique = $commande->historique()->first();
        $this->assertNull($historique->ancien_statut);
        $this->assertSame(StatutCommande::AttentePaiement, $historique->nouveau_statut);

        $rejoue = $service->executer($etablissement, $client, $lignes, Canal::Web, SourceCommande::PanierWeb, 'cle-variante');
        $this->assertSame($commande->id, $rejoue->id);
        $this->assertSame(3, $variante->fresh()->quantite_reservee, 'rejouer ne doit pas réserver deux fois');
        $this->assertSame(1, $etablissement->commandes()->count());
    }

    /**
     * Règle du catalogue : un produit sans variante porte son propre stock.
     * La plupart des produits d'une boutique n'ont aucune variante — ce
     * chemin, pas celui avec variante, est le cas courant.
     */
    public function test_mode_compte_sans_variante_reserve_sur_le_produit_lui_meme(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);
        $this->assertTrue($produit->variantes->isEmpty());

        $commande = app(CreerCommande::class)->executer(
            $etablissement,
            $client,
            [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web,
            SourceCommande::PanierWeb,
            'cle-sans-variante',
        );

        $this->assertSame(1, $commande->reservations()->count());
        $reservation = $commande->reservations()->first();
        $this->assertSame($produit->id, $reservation->produit_id);
        $this->assertNull($reservation->variante_id);
        $this->assertSame(4, $produit->fresh()->quantite_reservee);
    }

    public function test_mode_interrupteur_ne_cree_aucune_reservation(): void
    {
        $etablissement = Etablissement::factory()->restaurant()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Resto']);
        $produit = Produit::factory()->for($etablissement)->create(['disponible' => true]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement,
            $client,
            [new LigneCommandeDemandee($produit->id, null, 1)],
            Canal::Whatsapp,
            SourceCommande::BoutonWhatsapp,
            'cle-interrupteur',
        );

        $this->assertSame(1, $commande->lignes()->count());
        $this->assertSame(0, $commande->reservations()->count());
    }

    public function test_stock_insuffisant_sur_variante_leve_et_ne_persiste_rien(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 2, 'quantite_reservee' => 0]);

        try {
            app(CreerCommande::class)->executer(
                $etablissement, $client,
                [new LigneCommandeDemandee($produit->id, null, 5)],
                Canal::Web, SourceCommande::PanierWeb, 'cle-insuffisant-variante',
            );
            $this->fail('ArticleIndisponibleException attendue');
        } catch (ArticleIndisponibleException $e) {
            $this->assertStringContainsString($produit->nom, $e->nomArticle());
        }

        $this->assertSame(0, $etablissement->commandes()->count());
        $this->assertSame(0, $variante->fresh()->quantite_reservee);
    }

    public function test_stock_insuffisant_sur_produit_sans_variante_leve_et_ne_persiste_rien(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 2,
            'quantite_reservee' => 0,
        ]);

        $this->expectException(ArticleIndisponibleException::class);

        app(CreerCommande::class)->executer(
            $etablissement, $client,
            [new LigneCommandeDemandee($produit->id, null, 5)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-insuffisant-produit',
        );
    }

    public function test_variante_hors_produit_et_ambiguite_levent_sans_rien_persister(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produitA = Produit::factory()->for($etablissement)->avecVariantes(2)->create();
        $produitB = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $varianteDeB = $produitB->variantes()->first();

        $service = app(CreerCommande::class);

        try {
            $service->executer(
                $etablissement, $client,
                [new LigneCommandeDemandee($produitA->id, $varianteDeB->id, 1)],
                Canal::Web, SourceCommande::PanierWeb, 'cle-hors-produit',
            );
            $this->fail('SelectionVarianteInvalideException attendue');
        } catch (SelectionVarianteInvalideException) {
        }

        try {
            $service->executer(
                $etablissement, $client,
                [new LigneCommandeDemandee($produitA->id, null, 1)],
                Canal::Web, SourceCommande::PanierWeb, 'cle-ambigue',
            );
            $this->fail('SelectionVarianteInvalideException attendue');
        } catch (SelectionVarianteInvalideException) {
        }

        $this->assertSame(0, $etablissement->commandes()->count());
    }
}
