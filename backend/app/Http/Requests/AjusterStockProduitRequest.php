<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Étape 10 — distincte d'UpdateProduitRequest : n'accepte QUE la quantité en
 * stock. Un gestionnaire_stock (permission gerer_stock, pas gerer_catalogue)
 * n'a pas accès à update() ; même s'il envoyait un prix ou un nom ici, ces
 * champs ne sont tout simplement pas dans rules() et seraient ignorés par
 * validated() — la restriction n'est donc pas qu'un refus, c'est une
 * impossibilité structurelle.
 */
class AjusterStockProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('ajusterStock', $this->route('produit'));
    }

    public function rules(): array
    {
        return [
            'quantite_stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
