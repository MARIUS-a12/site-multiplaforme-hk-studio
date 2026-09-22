<?php

namespace Tests\Feature\Isolation;

use App\Models\Concerns\AppartientAEtablissement;
use App\Models\Etablissement;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

class AppartientAEtablissementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('produits_test', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etablissement_id')->constrained('etablissements')->cascadeOnDelete();
            $table->string('nom');
            $table->timestamps();
        });

        Etablissement::resolveRelationUsing(
            'produitsTest',
            fn (Etablissement $etablissement) => $etablissement->hasMany(ProduitFixture::class, 'etablissement_id'),
        );
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('produits_test');

        parent::tearDown();
    }

    public function test_impossible_de_lire_une_donnee_dun_autre_etablissement(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::create(['nom' => 'Etablissement A', 'slug' => 'etablissement-a', 'type' => 'boutique']);
        $etablissementB = Etablissement::create(['nom' => 'Etablissement B', 'slug' => 'etablissement-b', 'type' => 'boutique']);

        $contexte->definir($etablissementA);
        $produitA = ProduitFixture::create(['nom' => 'Produit A']);

        $contexte->definir($etablissementB);
        $produitB = ProduitFixture::create(['nom' => 'Produit B']);

        // Le reste du test se déroule dans le contexte de l'établissement A.
        $contexte->definir($etablissementA);

        // 1. Requête Eloquent classique : seul le produit de A doit apparaître.
        $this->assertSame([$produitA->id], ProduitFixture::pluck('id')->all());

        // 2. Accès direct par id : le produit de B reste invisible.
        $this->assertNull(ProduitFixture::find($produitB->id));

        // 3. À travers une relation : même depuis l'établissement B, le scope
        // global impose le contexte courant (A) et masque les produits de B.
        $this->assertTrue($etablissementB->produitsTest()->get()->isEmpty());
    }

    public function test_requete_hors_console_sans_contexte_leve_une_exception(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::create(['nom' => 'Etablissement A', 'slug' => 'etablissement-a', 'type' => 'boutique']);
        $etablissementB = Etablissement::create(['nom' => 'Etablissement B', 'slug' => 'etablissement-b', 'type' => 'boutique']);

        $contexte->definir($etablissementA);
        ProduitFixture::create(['nom' => 'Produit A']);

        $contexte->definir($etablissementB);
        ProduitFixture::create(['nom' => 'Produit B']);

        // Aucun établissement courant : le scope ne doit jamais se contenter
        // de ne rien filtrer, sous peine d'exposer les deux établissements.
        $contexte->definir(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Aucun établissement n'est défini");

        $this->horsConsole(fn () => ProduitFixture::all());
    }

    public function test_update_et_delete_en_masse_naffectent_pas_lautre_etablissement(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::create(['nom' => 'Etablissement A', 'slug' => 'etablissement-a', 'type' => 'boutique']);
        $etablissementB = Etablissement::create(['nom' => 'Etablissement B', 'slug' => 'etablissement-b', 'type' => 'boutique']);

        $contexte->definir($etablissementA);
        $produitA = ProduitFixture::create(['nom' => 'Produit A']);

        $contexte->definir($etablissementB);
        $produitB = ProduitFixture::create(['nom' => 'Produit B']);

        // Le reste du test se déroule dans le contexte de l'établissement A.
        $contexte->definir($etablissementA);

        ProduitFixture::query()->update(['nom' => 'Modifié']);

        $this->assertSame('Modifié', $produitA->fresh()->nom);
        $this->assertSame('Produit B', ProduitFixture::pourTousEtablissements()->find($produitB->id)->nom);

        ProduitFixture::query()->delete();

        $this->assertNull(ProduitFixture::pourTousEtablissements()->find($produitA->id));
        $this->assertNotNull(ProduitFixture::pourTousEtablissements()->find($produitB->id));
    }

    public function test_creation_renseigne_automatiquement_etablissement_id_du_contexte(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::create(['nom' => 'Etablissement A', 'slug' => 'etablissement-a', 'type' => 'boutique']);
        $etablissementB = Etablissement::create(['nom' => 'Etablissement B', 'slug' => 'etablissement-b', 'type' => 'boutique']);

        $contexte->definir($etablissementA);

        $produit = ProduitFixture::create(['nom' => 'Produit A']);
        $this->assertSame($etablissementA->id, $produit->etablissement_id);

        // etablissement_id n'est pas fillable : une tentative d'assignation de
        // masse est ignorée, seul le trait (via l'événement "creating") a la
        // main dessus.
        $produitForce = ProduitFixture::create(['nom' => 'Produit forcé', 'etablissement_id' => $etablissementB->id]);
        $this->assertSame($etablissementA->id, $produitForce->etablissement_id);
    }

    public function test_pour_tous_etablissements_est_seule_a_exposer_les_deux_etablissements(): void
    {
        $contexte = app(ContexteEtablissement::class);

        $etablissementA = Etablissement::create(['nom' => 'Etablissement A', 'slug' => 'etablissement-a', 'type' => 'boutique']);
        $etablissementB = Etablissement::create(['nom' => 'Etablissement B', 'slug' => 'etablissement-b', 'type' => 'boutique']);

        $contexte->definir($etablissementA);
        $produitA = ProduitFixture::create(['nom' => 'Produit A']);

        $contexte->definir($etablissementB);
        $produitB = ProduitFixture::create(['nom' => 'Produit B']);

        $contexte->definir($etablissementA);

        // La requête normale reste cantonnée à l'établissement courant (A).
        $this->assertSame([$produitA->id], ProduitFixture::pluck('id')->all());

        // Seule la méthode d'échappement super-admin expose les deux établissements.
        $this->assertEqualsCanonicalizing(
            [$produitA->id, $produitB->id],
            ProduitFixture::pourTousEtablissements()->pluck('id')->all()
        );
    }

    /**
     * Force temporairement l'application hors du mode console pour la durée
     * du callback, afin de simuler le comportement du scope tel qu'il
     * s'exécute réellement lors d'une requête HTTP (le process de test
     * PHPUnit, lui, tourne toujours en CLI).
     */
    private function horsConsole(callable $callback): mixed
    {
        $proprieteConsole = new ReflectionProperty($this->app, 'isRunningInConsole');
        $valeurOriginale = $proprieteConsole->getValue($this->app);
        $proprieteConsole->setValue($this->app, false);

        try {
            return $callback();
        } finally {
            $proprieteConsole->setValue($this->app, $valeurOriginale);
        }
    }
}

class ProduitFixture extends Model
{
    use AppartientAEtablissement;

    protected $table = 'produits_test';

    protected $fillable = ['nom'];
}
