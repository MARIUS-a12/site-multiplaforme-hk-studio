<?php

namespace App\Policies;

use App\Models\Categorie;
use App\Models\User;

class CategoriePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->peut('gerer_catalogue');
    }

    public function view(User $user, Categorie $categorie): bool
    {
        return $user->peut('gerer_catalogue');
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
