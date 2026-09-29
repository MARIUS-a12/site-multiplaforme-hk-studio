<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\EtablissementUtilisateur;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Quatre comptes de démo, un mot de passe identique en clair (données de
 * démo uniquement, jamais en production) : voir la table de l'Étape 1.
 * S'appuie sur les établissements déjà créés par EtablissementsDemoSeeder
 * (slugs "chez-awa" et "maquis-du-port") — à exécuter après lui.
 */
class UtilisateursDemoSeeder extends Seeder
{
    private const MOT_DE_PASSE = 'motdepasse';

    public function run(): void
    {
        $chezAwa = Etablissement::where('slug', 'chez-awa')->firstOrFail();
        $maquisDuPort = Etablissement::where('slug', 'maquis-du-port')->firstOrFail();

        $roleAdminEtablissement = Role::where('nom', 'admin_etablissement')->value('id');
        $roleOperateur = Role::where('nom', 'operateur')->value('id');

        $marius = User::factory()->create([
            'name' => 'Marius Kouame',
            'email' => 'super@plateforme.test',
            'password' => self::MOT_DE_PASSE,
        ]);
        // est_super_admin n'est pas fillable (voir User) : assignation
        // directe, volontairement, pas via une mise à jour de masse.
        $marius->est_super_admin = true;
        $marius->save();

        $awa = User::factory()->create([
            'name' => 'Awa Traoré',
            'email' => 'awa@chez-awa.test',
            'password' => self::MOT_DE_PASSE,
        ]);
        EtablissementUtilisateur::create([
            'etablissement_id' => $chezAwa->id,
            'utilisateur_id' => $awa->id,
            'role_id' => $roleAdminEtablissement,
            'statut' => 'actif',
        ]);

        $yao = User::factory()->create([
            'name' => 'Yao Kouadio',
            'email' => 'yao@maquis-du-port.test',
            'password' => self::MOT_DE_PASSE,
        ]);
        EtablissementUtilisateur::create([
            'etablissement_id' => $maquisDuPort->id,
            'utilisateur_id' => $yao->id,
            'role_id' => $roleOperateur,
            'statut' => 'actif',
        ]);

        // Admin du restaurant : sans elle, impossible de tester le
        // formulaire produit en mode interrupteur avec un compte qui a
        // gerer_catalogue — Awa est sur une boutique, Yao n'est qu'opérateur.
        $adjoua = User::factory()->create([
            'name' => 'Adjoua Kone',
            'email' => 'adjoua@maquis-du-port.test',
            'password' => self::MOT_DE_PASSE,
        ]);
        EtablissementUtilisateur::create([
            'etablissement_id' => $maquisDuPort->id,
            'utilisateur_id' => $adjoua->id,
            'role_id' => $roleAdminEtablissement,
            'statut' => 'actif',
        ]);
    }
}
