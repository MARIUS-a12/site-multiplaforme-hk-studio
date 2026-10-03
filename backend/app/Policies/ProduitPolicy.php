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

    /**
     * Étape 10 — distincte de update() : gerer_stock n'autorise QUE
     * l'ajustement de quantité (voir AjusterStockProduitRequest, qui
     * n'accepte aucun autre champ), jamais le prix, le nom, ni le statut.
     * gerer_catalogue continue de tout permettre, y compris ceci.
     */
    public function ajusterStock(User $user, Produit $produit): bool
    {
        return $user->peut('gerer_catalogue') || $user->peut('gerer_stock');
    }
}
