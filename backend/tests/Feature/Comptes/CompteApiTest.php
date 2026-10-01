<?php

namespace Tests\Feature\Comptes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Étape 7 — "Mon compte" : changement de mot de passe (avec invalidation des
 * autres sessions) et de profil (nom/email). Réutilise les comptes de démo
 * (voir UtilisateursDemoSeeder) : Awa est admin de chez-awa, Yao n'est
 * qu'opérateur sur maquis-du-port.
 */
class CompteApiTest extends TestCase
{
    use RefreshDatabase;

    private const MOT_DE_PASSE = 'motdepasse';

    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    private function connecte(string $hote, string $email): static
    {
        $this->depuis($hote)->postJson("http://{$hote}:8000/api/connexion", [
            'email' => $email,
            'mot_de_passe' => self::MOT_DE_PASSE,
        ])->assertStatus(200);

        return $this;
    }

    public function test_1_changement_sans_mot_de_passe_actuel_refuse(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
            'nouveau_mot_de_passe' => 'un-nouveau-mot-de-passe',
            'nouveau_mot_de_passe_confirmation' => 'un-nouveau-mot-de-passe',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonValidationErrors(['mot_de_passe_actuel']);
    }

    public function test_2a_mot_de_passe_trop_court_refuse(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
            'mot_de_passe_actuel' => self::MOT_DE_PASSE,
            'nouveau_mot_de_passe' => 'court1',
            'nouveau_mot_de_passe_confirmation' => 'court1',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonValidationErrors(['nouveau_mot_de_passe']);
    }

    public function test_2b_mot_de_passe_trop_courant_refuse(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $motDePasseCourant = 'password123';
        $suffixe = substr(strtoupper(sha1($motDePasseCourant)), 5);
        Http::fake(['api.pwnedpasswords.com/*' => Http::response("{$suffixe}:12345", 200)]);

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
            'mot_de_passe_actuel' => self::MOT_DE_PASSE,
            'nouveau_mot_de_passe' => $motDePasseCourant,
            'nouveau_mot_de_passe_confirmation' => $motDePasseCourant,
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonValidationErrors(['nouveau_mot_de_passe']);
    }

    /**
     * La mécanique exacte de suppression des AUTRES sessions (en gardant
     * précisément la sienne) est testée en isolation, sans HTTP, dans
     * ModifierMotDePasseUtilisateurTest — SESSION_DRIVER=array en test (voir
     * phpunit.xml) ne persiste jamais la session courante d'une requête HTTP
     * réelle dans la table "sessions", ce qui rend impossible de retrouver
     * ici l'identifiant exact de "sa propre" session via un aller-retour
     * complet. Ce test-ci vérifie donc ce qui EST observable côté API :
     * l'appareil tiers est bien coupé, et la session qui vient de changer le
     * mot de passe continue, elle, de fonctionner (pas d'auto-verrouillage).
     */
    public function test_3_autres_sessions_invalidees_la_sienne_reste(): void
    {
        $this->seed();
        $awa = User::where('email', 'awa@chez-awa.test')->firstOrFail();

        // Une session "autre" posée directement en base, comme le ferait un
        // deuxième appareil déjà connecté.
        DB::table('sessions')->insert([
            'id' => 'session-autre-appareil',
            'user_id' => $awa->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => base64_encode('donnees'),
            'last_activity' => time(),
        ]);

        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
            'mot_de_passe_actuel' => self::MOT_DE_PASSE,
            'nouveau_mot_de_passe' => 'un-nouveau-mot-de-passe-solide',
            'nouveau_mot_de_passe_confirmation' => 'un-nouveau-mot-de-passe-solide',
        ]);

        $reponse->assertStatus(204);

        $this->assertDatabaseMissing('sessions', ['id' => 'session-autre-appareil']);

        // La session en cours reste valide : /api/moi répond toujours.
        $this->depuis('chez-awa.localhost')->getJson('http://chez-awa.localhost:8000/api/moi')->assertStatus(200);
    }

    public function test_4_sixieme_tentative_en_une_heure_bloquee(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        for ($i = 0; $i < 5; $i++) {
            $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
                'mot_de_passe_actuel' => 'mauvais-mot-de-passe',
                'nouveau_mot_de_passe' => 'peu-importe-ici-123',
                'nouveau_mot_de_passe_confirmation' => 'peu-importe-ici-123',
            ])->assertStatus(422);
        }

        $sixieme = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/mot-de-passe', [
            'mot_de_passe_actuel' => 'mauvais-mot-de-passe',
            'nouveau_mot_de_passe' => 'peu-importe-ici-123',
            'nouveau_mot_de_passe_confirmation' => 'peu-importe-ici-123',
        ]);

        $sixieme->assertStatus(429);
    }

    public function test_5_changement_email_sans_mot_de_passe_actuel_refuse(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/profil', [
            'nom' => 'Awa Traoré',
            'email' => 'nouvel-email@chez-awa.test',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonValidationErrors(['mot_de_passe_actuel']);
    }

    public function test_5b_changement_nom_seul_sans_mot_de_passe_accepte(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $reponse = $this->depuis('chez-awa.localhost')->patchJson('http://chez-awa.localhost:8000/api/compte/profil', [
            'nom' => 'Awa T. Traoré',
            'email' => 'awa@chez-awa.test',
        ]);

        $reponse->assertStatus(200);
        $this->assertSame('Awa T. Traoré', User::where('email', 'awa@chez-awa.test')->value('name'));
    }

    public function test_10_operateur_accede_a_compte(): void
    {
        $this->seed();
        $this->connecte('maquis-du-port.localhost', 'yao@maquis-du-port.test');

        // "Mon compte" n'exige aucune permission particulière : seulement
        // d'être authentifié, quel que soit le rôle.
        $reponse = $this->depuis('maquis-du-port.localhost')->patchJson('http://maquis-du-port.localhost:8000/api/compte/profil', [
            'nom' => 'Yao K.',
            'email' => 'yao@maquis-du-port.test',
        ]);

        $reponse->assertStatus(200);
    }
}
