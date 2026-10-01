<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Le mot de passe actuel est obligatoire et vérifié côté serveur (règle
 * "current_password", contre le guard "web") : sans lui, quiconque accède à
 * une session déjà ouverte pourrait verrouiller le compte. Aucune règle de
 * composition (majuscule, chiffre, symbole) au-delà de la longueur et du
 * refus des mots de passe compromis : ce genre de contrainte produit des
 * mots de passe plus faibles, notés sur un papier.
 */
class ModifierMotDePasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mot_de_passe_actuel' => ['required', 'current_password'],
            'nouveau_mot_de_passe' => ['required', 'confirmed', Password::min(8)->uncompromised()],
        ];
    }

    public function messages(): array
    {
        return [
            'mot_de_passe_actuel.current_password' => 'Le mot de passe actuel est incorrect.',
            'nouveau_mot_de_passe.uncompromised' => "Ce mot de passe apparaît dans une fuite de données connue. Choisissez-en un autre.",
        ];
    }
}
