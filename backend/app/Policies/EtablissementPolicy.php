<?php

namespace App\Policies;

use App\Models\User;

/**
 * Liste des établissements de la plateforme : réservée au super-admin, qui
 * la contourne via Gate::before (AppServiceProvider) avant même d'atteindre
 * cette policy. viewAny() ne renvoie donc jamais true ici — si un jour un
 * autre rôle doit y accéder, ce sera un choix explicite, pas un oubli.
 */
class EtablissementPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }
}
