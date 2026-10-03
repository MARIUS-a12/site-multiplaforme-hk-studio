<?php

namespace App\Http\Requests\Activation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validation de FORME uniquement (email, code à 8 caractères, mot de passe).
 * La correspondance réelle (le code existe-t-il, pour cet email, dans cet
 * établissement, non expiré, non utilisé) est décidée par ActiverCompte
 * seul — jamais ici, pour ne pas dupliquer cette logique à deux endroits.
 */
class ActiverCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim((string) $this->input('email', '')),
            'code' => trim((string) $this->input('code', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:8'],
            'nouveau_mot_de_passe' => ['required', 'confirmed', Password::min(8)->uncompromised()],
        ];
    }

    public function messages(): array
    {
        return [
            'nouveau_mot_de_passe.uncompromised' => "Ce mot de passe apparaît dans une fuite de données connue. Choisissez-en un autre.",
        ];
    }
}
