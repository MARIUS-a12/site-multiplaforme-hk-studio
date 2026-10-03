<?php

namespace App\Services\Equipe;

use App\Models\EtablissementUtilisateur;
use App\Models\JournalAudit;
use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Change le nom et/ou le rôle d'un membre — jamais son email (voir
 * StoreMembreEquipeRequest/UpdateMembreEquipeRequest : ce formulaire ne le
 * propose pas). Porte les deux garde-fous serveur qui dépendent de la CIBLE
 * et du changement demandé, pas seulement de qui demande (ça, c'est
 * EtablissementUtilisateurPolicy::update) :
 * - un administrateur ne peut pas changer son propre rôle ;
 * - retirer admin_etablissement au dernier administrateur actif de
 *   l'établissement est refusé.
 */
class ModifierMembreEquipe
{
    use GardeDernierAdministrateurActif;

    public function executer(
        EtablissementUtilisateur $membre,
        ?string $nom,
        ?int $roleId,
        User $acteur,
        ?string $adresseIp,
    ): EtablissementUtilisateur {
        $membre->loadMissing(['role', 'utilisateur']);

        $roleActuel = $membre->role;
        $roleChange = $roleId !== null && $roleId !== $membre->role_id;
        $nouveauRole = $roleChange ? Role::findOrFail($roleId) : null;

        if ($roleChange && $membre->utilisateur_id === $acteur->id) {
            throw ValidationException::withMessages([
                'role_id' => ['Vous ne pouvez pas modifier votre propre rôle.'],
            ]);
        }

        if (
            $roleChange
            && $roleActuel->nom === 'admin_etablissement'
            && $nouveauRole->nom !== 'admin_etablissement'
            && ! $this->existeAutreAdministrateurActif($membre)
        ) {
            throw ValidationException::withMessages([
                'role_id' => ['Cet établissement doit conserver au moins un administrateur actif.'],
            ]);
        }

        if ($nom !== null) {
            $membre->utilisateur->update(['name' => $nom]);
        }

        if ($roleChange) {
            $membre->update(['role_id' => $roleId]);
        }

        JournalAudit::create([
            'utilisateur_id' => $acteur->id,
            'action' => 'equipe_membre_modifie',
            'details' => array_filter([
                'membre_id' => $membre->utilisateur_id,
                'nom' => $nom,
                'role_precedent' => $roleChange ? $roleActuel->nom : null,
                'role_nouveau' => $roleChange ? $nouveauRole->nom : null,
            ], fn ($valeur) => $valeur !== null),
            'adresse_ip' => $adresseIp,
        ]);

        return $membre->refresh();
    }
}
