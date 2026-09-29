<?php

namespace App\Policies;

use App\Models\Etablissement;
use App\Models\User;

/**
 * Gestion des établissements de la plateforme : réservée au super-admin,
 * qui contourne toute cette policy via Gate::before (AppServiceProvider).
 * Aucune méthode ne renvoie jamais true ici — si un jour un autre rôle doit
 * y accéder, ce sera un choix explicite, pas un oubli.
 */
class EtablissementPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Etablissement $etablissement): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Etablissement $etablissement): bool
    {
        return false;
    }
}
