<?php

namespace App\Http\Requests\Vitrine;

use Illuminate\Foundation\Http\FormRequest;

/**
 * prix_vu est optionnel et purement informatif (voir VitrinePanierController)
 * : le dernier prix que LE NAVIGATEUR a affiché pour cette ligne, envoyé pour
 * que le serveur puisse signaler un changement — jamais utilisé pour calculer
 * un total, qui vient toujours du prix ACTUEL relu en base.
 */
class VerifierPanierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lignes' => ['present', 'array'],
            'lignes.*.produit_id' => ['required', 'integer'],
            'lignes.*.variante_id' => ['nullable', 'integer'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],
            'lignes.*.prix_vu' => ['nullable', 'integer'],
        ];
    }
}
