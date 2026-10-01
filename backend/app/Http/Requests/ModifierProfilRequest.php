<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Changer son nom ne demande rien de plus ; changer son email exige en plus
 * le mot de passe actuel (requis seulement quand l'email soumis diffère du
 * courant), pour la même raison que ModifierMotDePasseRequest.
 */
class ModifierProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'mot_de_passe_actuel' => [
                Rule::requiredIf(fn () => $this->input('email') !== $this->user()->email),
                'current_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'mot_de_passe_actuel.required' => "Le mot de passe actuel est requis pour changer d'email.",
            'mot_de_passe_actuel.current_password' => 'Le mot de passe actuel est incorrect.',
            'email.unique' => 'Cet email est déjà utilisé par un autre compte.',
        ];
    }
}
