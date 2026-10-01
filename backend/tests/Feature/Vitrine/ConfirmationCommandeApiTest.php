<?php

namespace Tests\Feature\Vitrine;

use App\Models\Etablissement;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 6B — GET /api/vitrine/commandes/{numero} (tests n°9 et 10 de la
 * spec) : le numéro seul se devine (CMD-000847), la page ne doit donc
 * jamais être lisible sans le jeton d'accès généré à la création — et
 * jamais avec le jeton d'une commande d'un AUTRE établissement.
 */
class ConfirmationCommandeApiTest extends TestCase
{
    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    use RefreshDatabase;

    private function chezAwa(): Etablissement
    {
        return Etablissement::where('slug', 'chez-awa')->firstOrFail();
    }

    private function creerCommande(string $hote, string $cle): array
    {
        $etablissement = Etablissement::where('slug', $hote === 'chez-awa.localhost' ? 'chez-awa' : 'maquis-du-port')->firstOrFail();
        $produit = Produit::factory()->for($etablissement)->publie()->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
            'disponible' => true,
        ]);

        $reponse = $this->depuis($hote)->postJson("http://{$hote}:8000/api/vitrine/commandes", [
            'lignes' => [['produit_id' => $produit->id, 'quantite' => 1]],
            'client' => ['nom' => 'Client Test', 'telephone' => '0701020304'],
            'cle_idempotence' => $cle,
        ]);

        $reponse->assertStatus(201);

        return ['numero' => $reponse->json('numero'), 'jeton' => $reponse->json('jeton')];
    }

    public function test_9_sans_jeton_renvoie_404(): void
    {
        $this->seed();
        $commande = $this->creerCommande('chez-awa.localhost', 'cle-confirmation-sans-jeton');

        $this->depuis('chez-awa.localhost')
            ->getJson("http://chez-awa.localhost:8000/api/vitrine/commandes/{$commande['numero']}")
            ->assertStatus(404);
    }

    public function test_10_avec_le_jeton_dun_autre_etablissement_renvoie_404(): void
    {
        $this->seed();
        $commandeAwa = $this->creerCommande('chez-awa.localhost', 'cle-confirmation-awa');
        $commandeMaquis = $this->creerCommande('maquis-du-port.localhost', 'cle-confirmation-maquis');

        // Jeton valide, mais vu depuis l'établissement de l'AUTRE commande.
        $this->depuis('maquis-du-port.localhost')
            ->getJson("http://maquis-du-port.localhost:8000/api/vitrine/commandes/{$commandeAwa['numero']}?jeton={$commandeAwa['jeton']}")
            ->assertStatus(404);

        // Numéro et jeton corrects, vus depuis le bon établissement : accessible.
        $this->depuis('chez-awa.localhost')
            ->getJson("http://chez-awa.localhost:8000/api/vitrine/commandes/{$commandeAwa['numero']}?jeton={$commandeAwa['jeton']}")
            ->assertStatus(200);

        $this->depuis('maquis-du-port.localhost')
            ->getJson("http://maquis-du-port.localhost:8000/api/vitrine/commandes/{$commandeMaquis['numero']}?jeton={$commandeMaquis['jeton']}")
            ->assertStatus(200);
    }
}
