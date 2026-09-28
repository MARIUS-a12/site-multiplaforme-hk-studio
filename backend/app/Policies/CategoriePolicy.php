<?php

namespace App\Policies;

use App\Models\Categorie;
use App\Models\User;

class CategoriePolicy
{
    /**
     * gerer_catalogue implique voir_catalogue, voir ProduitPolicy.
     */
    private function peutVoir(User $user): bool
    {
        return $user->peut('voir_catalogue') || $user->peut('gerer_catalogue');
    }

    public function viewAny(User $user): bool
    {
        return $this->peutVoir($user);
    }

    public function view(User $user, Categorie $categorie): bool
    {
        return $this->peutVoir($user);
    }

    public function create(User $user): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function update(User $user, Categorie $categorie): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function delete(User $user, Categorie $categorie): bool
    {
        return $user->peut('gerer_catalogue');
    }
}
