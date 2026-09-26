<?php

namespace App\Policies;

use App\Models\Produit;
use App\Models\User;

class ProduitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function view(User $user, Produit $produit): bool
    {
        return $user->peut('gerer_catalogue');
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
