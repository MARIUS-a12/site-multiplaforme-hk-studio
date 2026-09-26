<?php

namespace Tests\Feature\Auth;

use App\Models\Etablissement;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 1 — test 5, LE PLUS IMPORTANT : Awa, correctement authentifiée sur
 * chez-awa (admin_etablissement, a gerer_catalogue), appelle l'API avec
 * l'id d'un produit de Maquis du Port. 404 attendu, jamais 403 : la
 * différence compte — un 403 révèle que la ressource existe ailleurs, un
 * 404 ne révèle rien du tout.
 *
 * La garantie ne vient pas d'une vérification manuelle dans le contrôleur :
 * le binding implicite de route ({produit}) exécute Produit::query(), qui
 * inclut le scope global AppartientAEtablissement — sous le contexte
 * "chez-awa" posé par resoudre.etablissement, un produit de Maquis du Port
 * n'existe tout simplement pas pour cette requête. Awa a beau avoir la
 * permission gerer_catalogue : elle ne s'applique jamais, faute de
 * ressource à autoriser.
 */
class IsolationCrossTenantTest extends TestCase
{
    use RefreshDatabase;

    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    public function test_5_awa_ne_peut_ni_lire_ni_modifier_ni_supprimer_un_produit_de_maquis_du_port(): void
    {
        $this->seed();

        $maquisDuPort = Etablissement::where('slug', 'maquis-du-port')->firstOrFail();
        $produitMaquis = Produit::pourTousEtablissements()
            ->where('etablissement_id', $maquisDuPort->id)
            ->firstOrFail();

        $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ])->assertStatus(200);

        $get = $this->depuis('chez-awa.localhost')
            ->getJson("http://chez-awa.localhost:8000/api/produits/{$produitMaquis->id}");
        $get->assertStatus(404);

        $put = $this->depuis('chez-awa.localhost')
            ->putJson("http://chez-awa.localhost:8000/api/produits/{$produitMaquis->id}", ['nom' => 'Modifié par erreur']);
        $put->assertStatus(404);

        $delete = $this->depuis('chez-awa.localhost')
            ->deleteJson("http://chez-awa.localhost:8000/api/produits/{$produitMaquis->id}");
        $delete->assertStatus(404);

        // Le produit n'a subi aucune conséquence des tentatives.
        $this->assertNotNull(Produit::pourTousEtablissements()->find($produitMaquis->id));
    }
}
