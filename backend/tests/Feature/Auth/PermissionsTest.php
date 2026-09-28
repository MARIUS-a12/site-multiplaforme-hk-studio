<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 1 — test 4 : Yao (opérateur, voir_catalogue/voir_commandes/
 * gerer_commandes/gerer_clients uniquement) n'a pas gerer_parametres ni
 * voir_statistiques.
 */
class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    public function test_4_operateur_voit_les_commandes_mais_pas_les_parametres_ni_les_statistiques(): void
    {
        $this->seed();

        $this->depuis('maquis-du-port.localhost')->postJson('http://maquis-du-port.localhost:8000/api/connexion', [
            'email' => 'yao@maquis-du-port.test',
            'mot_de_passe' => 'motdepasse',
        ])->assertStatus(200);

        $this->depuis('maquis-du-port.localhost')
            ->getJson('http://maquis-du-port.localhost:8000/api/parametres')
            ->assertStatus(403);

        $this->depuis('maquis-du-port.localhost')
            ->getJson('http://maquis-du-port.localhost:8000/api/statistiques')
            ->assertStatus(403);

        $this->depuis('maquis-du-port.localhost')
            ->getJson('http://maquis-du-port.localhost:8000/api/commandes')
            ->assertStatus(200);
    }
}
