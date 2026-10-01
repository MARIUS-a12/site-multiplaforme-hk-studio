<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Une case à cocher ne suffit pas pour une action irréversible : le nom
 * exact de l'établissement doit être retapé. L'autorisation elle-même
 * (réservée au super-admin) est vérifiée dans le contrôleur, pas ici.
 */
class SupprimerEtablissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_confirmation' => [
                'required',
                'string',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($value !== $this->route('etablissement')->nom) {
                        $fail('Le nom saisi ne correspond pas exactement au nom de cet établissement.');
                    }
                },
            ],
        ];
    }
}
