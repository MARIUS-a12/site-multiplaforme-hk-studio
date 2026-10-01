<?php

namespace App\Services\Utilisateurs;

use App\Models\User;

class ModifierProfilUtilisateur
{
    public function executer(User $utilisateur, string $nom, string $email): User
    {
        $utilisateur->update(['name' => $nom, 'email' => $email]);

        return $utilisateur->fresh();
    }
}
