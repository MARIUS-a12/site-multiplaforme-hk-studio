<?php

namespace App\Policies;

use App\Models\EtablissementUtilisateur;
use App\Models\User;

/**
 * Étape 10 — page /admin/equipe : une seule permission, gerer_equipe,
 * réservée au seul rôle admin_etablissement (voir
 * RolesEtPermissionsSeeder). Les garde-fous fins (un administrateur ne peut
 * pas modifier son propre rôle ni se désactiver, un établissement garde
 * toujours au moins un administrateur actif) ne sont volontairement PAS ici :
 * ils dépendent de ce qui change et pas seulement de qui demande, et sont
 * donc portés par les services (CreerMembreEquipe, ModifierMembreEquipe,
 * ChangerStatutMembreEquipe), qui lèvent une ValidationException explicite.
 */
class EtablissementUtilisateurPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('gerer_equipe');
    }

    public function create(User $user): bool
    {
        return $user->peut('gerer_equipe');
    }

    public function update(User $user, EtablissementUtilisateur $membre): bool
    {
        return $user->peut('gerer_equipe');
    }

    public function changerStatut(User $user, EtablissementUtilisateur $membre): bool
    {
        return $user->peut('gerer_equipe');
    }

    public function reinitialiserMotDePasse(User $user, EtablissementUtilisateur $membre): bool
    {
        return $user->peut('gerer_equipe');
    }
}
