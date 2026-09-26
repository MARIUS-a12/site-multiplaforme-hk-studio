<?php

namespace Tests\Feature\Models;

use App\Enums\ModeStock;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Models\VarianteProduit;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class ProduitTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_disponibilite_sur_liste_eager_loadee_ne_genere_aucune_requete_supplementaire(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);

        // 5 produits sans variante (le stock vit sur le produit) et 5 avec
        // variantes (le stock vit sur chaque variante), pour exercer les
        // deux branches de estDisponibleEnQuantite() dans ce même test.
        Produit::factory()->for($etablissement)->count(5)->create();
        Produit::factory()->for($etablissement)->count(5)->avecVariantes(2)->create();

        // Chargement en amont, comme l'exige la documentation de la méthode.
        $produits = Produit::with('variantes')->get();
        $this->assertCount(10, $produits);

        DB::enableQueryLog();

        foreach ($produits as $produit) {
            $produit->estDisponibleEnQuantite(1);
        }

        $requetes = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(
            0,
            $requetes,
            "estDisponibleEnQuantite() a déclenché une requête alors que 'variantes' était déjà eager-loadée : ".
            json_encode(array_column($requetes, 'query'))
        );
    }

    public function test_creation_hors_contexte_sans_etablissement_resolu_leve_une_exception(): void
    {
        // Ni ContexteEtablissement défini, ni établissement résolu :
        // etablissement_id ne peut pas être déterminé, donc le type de
        // l'établissement (boutique/restaurant) non plus. On force
        // explicitement etablissement_id à null pour écarter le
        // sous-factory Etablissement::factory() par défaut de definition().
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Impossible de déduire mode_stock');

        Produit::factory()->make(['etablissement_id' => null])->save();
    }

    public function test_creation_hors_contexte_avec_mode_stock_explicite_naboutit_pas_a_une_exception(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();

        // Contexte volontairement non défini (simule une commande artisan) ;
        // seule la relation fixe etablissement_id, et mode_stock est fourni
        // explicitement : la déduction ne doit pas se déclencher.
        $produit = Produit::factory()->for($etablissement)->create([
            'mode_stock' => ModeStock::Interrupteur,
        ]);

        $this->assertSame(ModeStock::Interrupteur, $produit->mode_stock);
    }

    public function test_creation_via_relation_hors_contexte_deduit_correctement_le_mode_stock(): void
    {
        // Hors contexte (commande artisan, super-admin) mais via une
        // relation d'établissement : etablissement_id est résolu par la
        // relation, donc le type de l'établissement reste déterminable.
        $restaurant = Etablissement::factory()->restaurant()->create();

        $produit = Produit::factory()->for($restaurant)->create();

        $this->assertSame(ModeStock::Interrupteur, $produit->mode_stock);
    }

    public function test_produit_dun_etablissement_est_invisible_pour_un_autre_en_lecture_ecriture_et_relations(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::factory()->create();
        $etablissementB = Etablissement::factory()->create();

        $contexte->definir($etablissementA);
        $produitA = Produit::factory()->for($etablissementA)->create();

        $contexte->definir($etablissementB);
        $produitB = Produit::factory()->for($etablissementB)->create();

        $contexte->definir($etablissementA);

        // Lecture.
        $this->assertNotNull(Produit::find($produitA->id));
        $this->assertNull(Produit::find($produitB->id));

        // Écriture en masse : ne doit toucher que l'établissement courant.
        Produit::query()->update(['nom' => 'Modifié']);
        $this->assertSame('Modifié', $produitA->fresh()->nom);
        $this->assertNotSame('Modifié', Produit::pourTousEtablissements()->find($produitB->id)->nom);

        // À travers une relation : le scope global s'applique même en
        // partant de l'établissement B, et masque son propre produit tant
        // que le contexte courant reste A.
        $this->assertTrue($etablissementB->produits()->get()->isEmpty());
    }

    public function test_deux_produits_ne_peuvent_pas_partager_le_meme_slug_dans_le_meme_etablissement(): void
    {
        $etablissement = Etablissement::factory()->create();
        app(ContexteEtablissement::class)->definir($etablissement);

        Produit::factory()->for($etablissement)->create(['slug' => 'meme-slug']);

        $this->expectException(QueryException::class);

        Produit::factory()->for($etablissement)->create(['slug' => 'meme-slug']);
    }

    public function test_le_meme_slug_est_autorise_dans_deux_etablissements_differents(): void
    {
        $etablissementA = Etablissement::factory()->create();
        $etablissementB = Etablissement::factory()->create();

        $produitA = Produit::factory()->for($etablissementA)->create(['slug' => 'meme-slug']);
        $produitB = Produit::factory()->for($etablissementB)->create(['slug' => 'meme-slug']);

        $this->assertSame('meme-slug', $produitA->slug);
        $this->assertSame('meme-slug', $produitB->slug);
        $this->assertNotSame($produitA->id, $produitB->id);
    }

    public function test_mode_compte_calcule_la_disponibilite_a_partir_du_stock_et_des_reservations(): void
    {
        $boutique = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($boutique);

        $produit = Produit::factory()->for($boutique)->create([
            'quantite_stock' => 5,
            'quantite_reservee' => 3,
        ]);

        $this->assertSame(ModeStock::Compte, $produit->mode_stock);
        $this->assertTrue($produit->estDisponibleEnQuantite(2));
        $this->assertFalse($produit->estDisponibleEnQuantite(3));

        // Le disponible tombe à zéro : plus aucune quantité ne doit passer.
        $produit->quantite_reservee = 5;
        $this->assertFalse($produit->estDisponibleEnQuantite(1));
    }

    public function test_mode_interrupteur_ignore_les_quantites_et_ne_regarde_que_disponible(): void
    {
        $restaurant = Etablissement::factory()->restaurant()->create();
        app(ContexteEtablissement::class)->definir($restaurant);

        $produit = Produit::factory()->for($restaurant)->create([
            'quantite_stock' => 0,
            'quantite_reservee' => 0,
            'disponible' => true,
        ]);

        $this->assertSame(ModeStock::Interrupteur, $produit->mode_stock);
        // Les quantités sont ignorées : même à 0 en stock, disponible=true suffit.
        $this->assertTrue($produit->estDisponibleEnQuantite(999));

        $produit->disponible = false;
        $produit->quantite_stock = 999;
        // Même avec un stock élevé, disponible=false suffit à refuser.
        $this->assertFalse($produit->estDisponibleEnQuantite(1));
    }

    public function test_produit_avec_variantes_ne_consulte_jamais_son_propre_stock(): void
    {
        $boutique = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($boutique);

        $produit = Produit::factory()->for($boutique)->create([
            'quantite_stock' => 0,
            'quantite_reservee' => 0,
            'disponible' => false,
        ]);

        VarianteProduit::factory()->pourProduit($produit)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 2,
        ]);

        // Le produit lui-même est à 0/indisponible : si son propre stock
        // était consulté, ceci renverrait false. La variante, elle, a du
        // stock disponible.
        $this->assertTrue($produit->fresh()->estDisponibleEnQuantite(5));
    }

    public function test_scope_publies_exclut_les_brouillons(): void
    {
        $etablissement = Etablissement::factory()->create();
        app(ContexteEtablissement::class)->definir($etablissement);

        $publie = Produit::factory()->for($etablissement)->publie()->create();
        $brouillon = Produit::factory()->for($etablissement)->brouillon()->create();

        $idsPublies = Produit::publies()->pluck('id');

        $this->assertTrue($idsPublies->contains($publie->id));
        $this->assertFalse($idsPublies->contains($brouillon->id));
    }
}
