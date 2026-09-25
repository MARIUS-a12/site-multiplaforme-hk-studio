<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesEtPermissionsSeeder extends Seeder
{
    private const PERMISSIONS = [
        'gerer_catalogue' => 'Gérer le catalogue',
        'voir_commandes' => 'Voir les commandes',
        'gerer_commandes' => 'Gérer les commandes',
        'gerer_clients' => 'Gérer les clients',
        'voir_statistiques' => 'Voir les statistiques',
        'gerer_parametres' => 'Gérer les paramètres',
        'gerer_integrations' => 'Gérer les intégrations',
    ];

    private const ROLES = [
        'super_admin' => 'Super administrateur',
        'admin_etablissement' => "Administrateur d'établissement",
        'operateur' => 'Opérateur',
    ];

    /**
     * super_admin et admin_etablissement ont les 7 mêmes permissions, mais
     * restent deux rôles distincts : super_admin n'est jamais rattaché à un
     * établissement (voir EtablissementUtilisateur) et contourne les
     * policies via Gate::before, indépendamment de cette table.
     */
    private const ATTRIBUTIONS = [
        'super_admin' => [
            'gerer_catalogue', 'voir_commandes', 'gerer_commandes', 'gerer_clients',
            'voir_statistiques', 'gerer_parametres', 'gerer_integrations',
        ],
        'admin_etablissement' => [
            'gerer_catalogue', 'voir_commandes', 'gerer_commandes', 'gerer_clients',
            'voir_statistiques', 'gerer_parametres', 'gerer_integrations',
        ],
        'operateur' => ['voir_commandes', 'gerer_commandes', 'gerer_clients'],
    ];

    public function run(): void
    {
        $permissions = collect(self::PERMISSIONS)->map(
            fn (string $libelle, string $nom) => Permission::firstOrCreate(['nom' => $nom], ['libelle' => $libelle])
        );

        foreach (self::ROLES as $nomRole => $libelleRole) {
            $role = Role::firstOrCreate(['nom' => $nomRole], ['libelle' => $libelleRole]);

            $role->permissions()->sync(
                $permissions->only(self::ATTRIBUTIONS[$nomRole])->pluck('id')
            );
        }
    }
}
