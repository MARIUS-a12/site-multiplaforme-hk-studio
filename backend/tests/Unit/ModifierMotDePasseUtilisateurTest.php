<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Utilisateurs\ModifierMotDePasseUtilisateur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Teste directement le service d'invalidation de sessions, sans passer par
 * une connexion HTTP réelle : en test, SESSION_DRIVER=array (voir
 * phpunit.xml) ne persiste jamais la session courante dans la table
 * "sessions", ce qui rendrait un aller-retour HTTP complet incapable de
 * vérifier ce comportement — voir CompteApiTest::test_3 pour la partie
 * observable depuis l'API (la session en cours continue de répondre).
 */
class ModifierMotDePasseUtilisateurTest extends TestCase
{
    use RefreshDatabase;

    public function test_supprime_les_autres_sessions_et_garde_la_sienne(): void
    {
        $utilisateur = User::factory()->create(['password' => 'ancien-mot-de-passe']);

        DB::table('sessions')->insert([
            ['id' => 'session-courante', 'user_id' => $utilisateur->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => base64_encode('a'), 'last_activity' => time()],
            ['id' => 'session-autre-appareil', 'user_id' => $utilisateur->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => base64_encode('b'), 'last_activity' => time()],
            ['id' => 'session-dun-autre-utilisateur', 'user_id' => $utilisateur->id + 1, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => base64_encode('c'), 'last_activity' => time()],
        ]);

        (new ModifierMotDePasseUtilisateur())->executer(
            utilisateur: $utilisateur,
            nouveauMotDePasse: 'un-nouveau-mot-de-passe',
            idSessionActuelle: 'session-courante',
            adresseIp: '203.0.113.1',
        );

        $this->assertDatabaseHas('sessions', ['id' => 'session-courante']);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-autre-appareil']);
        // Une session d'un AUTRE utilisateur n'a rien à voir avec ce
        // changement : la requête ne doit filtrer que sur l'utilisateur
        // concerné, jamais purger au-delà.
        $this->assertDatabaseHas('sessions', ['id' => 'session-dun-autre-utilisateur']);

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('un-nouveau-mot-de-passe', $utilisateur->fresh()->password));

        $this->assertDatabaseHas('journaux_audit', [
            'utilisateur_id' => $utilisateur->id,
            'action' => 'mot_de_passe_modifie',
            'adresse_ip' => '203.0.113.1',
        ]);
    }
}
