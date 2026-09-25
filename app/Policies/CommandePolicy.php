<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('voir_commandes');
    }

    public function view(User $user, Commande $commande): bool
    {
        return $user->peut('voir_commandes');
    }

    public function create(User $user): bool
    {
        return $user->peut('gerer_commandes');
    }

    public function update(User $user, Commande $commande): bool
    {
        return $user->peut('gerer_commandes');
    }

    public function delete(User $user, Commande $commande): bool
    {
        return $user->peut('gerer_commandes');
    }
}
