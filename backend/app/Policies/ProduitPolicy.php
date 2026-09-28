<?php

namespace App\Policies;

use App\Models\Produit;
use App\Models\User;

class ProduitPolicy
{
    /**
     * gerer_catalogue implique voir_catalogue : un rôle qui peut modifier
     * le catalogue peut forcément aussi le consulter, sans avoir besoin des
     * deux permissions listées séparément dans le seeder.
     */
    private function peutVoir(User $user): bool
    {
        return $user->peut('voir_catalogue') || $user->peut('gerer_catalogue');
    }

    public function viewAny(User $user): bool
    {
        return $this->peutVoir($user);
    }

    public function view(User $user, Produit $produit): bool
    {
        return $this->peutVoir($user);
    }

    public function create(User $user): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function update(User $user, Produit $produit): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function delete(User $user, Produit $produit): bool
    {
        return $user->peut('gerer_catalogue');
    }
}
