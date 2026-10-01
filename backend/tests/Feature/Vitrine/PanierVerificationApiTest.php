<?php

namespace Tests\Feature\Vitrine;

use App\Models\Etablissement;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 6B — POST /api/vitrine/panier/verifier (test n°11 de la spec) : un
 * panier peut dater de plusieurs jours, cette route relit l'état réel de
 * chaque ligne plutôt que de faire confiance à ce que le navigateur a gardé.
 */
class PanierVerificationApiTest extends TestCase
{
    use RefreshDatabase;

    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    private function chezAwa(): Etablissement
    {
        return Etablissement::where('slug', 'chez-awa')->firstOrFail();
    }

    public function test_11_signale_une_ligne_dont_le_prix_a_change_et_une_ligne_archivee(): void
    {
        $this->seed();
        $chezAwa = $this->chezAwa();
        $produitPrixChange = Produit::factory()->for($chezAwa)->publie()->create(['prix' => 3000]);
        $produitArchive = Produit::factory()->for($chezAwa)->create(['statut' => 'archive']);
        $produitInchange = Produit::factory()->for($chezAwa)->publie()->create([
            'prix' => 1500,
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $reponse = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/vitrine/panier/verifier', [
            'lignes' => [
                ['produit_id' => $produitPrixChange->id, 'quantite' => 1, 'prix_vu' => 2000],
                ['produit_id' => $produitArchive->id, 'quantite' => 1],
                ['produit_id' => $produitInchange->id, 'quantite' => 2, 'prix_vu' => 1500],
            ],
        ]);

        $reponse->assertStatus(200);
        $lignes = $reponse->json('lignes');

        $this->assertSame('prix_modifie', $lignes[0]['statut']);
        $this->assertSame(3000, $lignes[0]['prix_actuel']);

        $this->assertSame('retire', $lignes[1]['statut']);

        $this->assertSame('disponible', $lignes[2]['statut']);

        // Sous-total : seules les lignes encore commandables comptent (3000 + 2×1500).
        $this->assertSame(6000, $reponse->json('sous_total'));
    }

    public function test_signale_une_ligne_epuisee_sans_la_faire_disparaitre(): void
    {
        $this->seed();
        $produit = Produit::factory()->for($this->chezAwa())->publie()->epuise()->create();

        $reponse = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/vitrine/panier/verifier', [
            'lignes' => [['produit_id' => $produit->id, 'quantite' => 1]],
        ]);

        $reponse->assertStatus(200);
        $this->assertSame('epuise', $reponse->json('lignes.0.statut'));
        $this->assertSame(0, $reponse->json('sous_total'));
    }
}
