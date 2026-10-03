<?php

namespace Tests\Feature\Auth;

use App\Models\Etablissement;
use App\Models\EtablissementUtilisateur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Étape 1 — Authentification et permissions, tests 1, 2, 3, 6, 7.
 *
 * Un test = un hôte = un parcours : le client de test HTTP de Laravel
 * rejoue tous les cookies de tous les hôtes précédents sur chaque nouvelle
 * requête (aucun cloisonnement par domaine, contrairement à un vrai
 * navigateur) — mélanger plusieurs hôtes dans un seul test produirait de
 * fausses authentifications croisées qui n'existent pas en réalité.
 */
class ConnexionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Un vrai navigateur envoie toujours Origin/Referer en cross-origin :
     * EnsureFrontendRequestsAreStateful (donc la session) en dépend. Le port
     * :8000 correspond à l'entrée `*.localhost:8000` de
     * SANCTUM_STATEFUL_DOMAINS (voir .env) — sans lui, aucune entrée ne
     * correspond à un hôte de test sans port explicite.
     */
    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    // Étape 10 : admin_etablissement a reçu gerer_stock et gerer_equipe en
    // plus de ses 8 permissions d'origine (voir RolesEtPermissionsSeeder) —
    // 10 désormais.
    public function test_1_connexion_reussie_et_moi_renvoie_role_et_ses_10_permissions(): void
    {
        $this->seed();

        $connexion = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);
        $connexion->assertStatus(200);

        $moi = $this->depuis('chez-awa.localhost')->getJson('http://chez-awa.localhost:8000/api/moi');
        $moi->assertStatus(200);
        $moi->assertJsonPath('role', 'admin_etablissement');
        $this->assertCount(10, $moi->json('permissions'));
    }

    public function test_2_mauvais_sous_domaine_est_refuse_meme_avec_le_bon_mot_de_passe(): void
    {
        $this->seed();

        // Awa a un compte valide sur chez-awa, mais aucune appartenance sur
        // maquis-du-port : son mot de passe, pourtant correct, ne suffit pas.
        $reponse = $this->depuis('maquis-du-port.localhost')->postJson('http://maquis-du-port.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonPath('errors.email.0', 'Identifiants invalides.');
    }

    public function test_3_appartenance_suspendue_est_refusee(): void
    {
        $this->seed();

        $awa = User::where('email', 'awa@chez-awa.test')->firstOrFail();
        EtablissementUtilisateur::where('utilisateur_id', $awa->id)->update(['statut' => 'suspendu']);

        $reponse = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonPath('errors.email.0', 'Identifiants invalides.');
    }

    public function test_3_etablissement_inactif_est_refuse(): void
    {
        $this->seed();

        Etablissement::where('slug', 'chez-awa')->update(['statut' => 'inactif']);

        $reponse = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponse->assertStatus(422);
        $reponse->assertJsonPath('errors.email.0', 'Identifiants invalides.');
    }

    public function test_6_la_sixieme_tentative_de_connexion_en_une_minute_est_limitee(): void
    {
        $this->seed();

        for ($i = 0; $i < 5; $i++) {
            $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
                'email' => 'awa@chez-awa.test',
                'mot_de_passe' => 'faux',
            ])->assertStatus(422);
        }

        $sixieme = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'faux',
        ]);

        $sixieme->assertStatus(429);
    }

    public function test_7_les_4_causes_dechec_renvoient_exactement_le_meme_corps_et_le_meme_code(): void
    {
        $this->seed();

        $awa = User::where('email', 'awa@chez-awa.test')->firstOrFail();

        // Cause 1 : mauvais mot de passe.
        $mauvaisMdp = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'faux',
        ]);

        // Cause 2 : mauvais sous-domaine (mot de passe correct).
        $mauvaisSousDomaine = $this->depuis('maquis-du-port.localhost')->postJson('http://maquis-du-port.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        // Cause 3 : appartenance suspendue.
        EtablissementUtilisateur::where('utilisateur_id', $awa->id)->update(['statut' => 'suspendu']);
        $suspendue = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);
        EtablissementUtilisateur::where('utilisateur_id', $awa->id)->update(['statut' => 'actif']);

        // Cause 4 : établissement inactif.
        Etablissement::where('slug', 'chez-awa')->update(['statut' => 'inactif']);
        $etablissementInactif = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/connexion', [
            'email' => 'awa@chez-awa.test',
            'mot_de_passe' => 'motdepasse',
        ]);

        $reponses = [$mauvaisMdp, $mauvaisSousDomaine, $suspendue, $etablissementInactif];

        foreach ($reponses as $reponse) {
            $reponse->assertStatus(422);
            $reponse->assertJsonPath('errors.email.0', 'Identifiants invalides.');
        }

        $corps = array_map(fn ($r) => $r->getContent(), $reponses);
        $this->assertCount(1, array_unique($corps), 'les 4 causes doivent produire exactement le même corps de réponse');
    }
}
