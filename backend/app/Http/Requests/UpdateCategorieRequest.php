<?php

namespace App\Http\Requests;

use App\Enums\StatutCategorie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('categorie'));
    }

    /**
     * nom ne régénère jamais slug en modification, voir UpdateProduitRequest.
     */
    public function rules(): array
    {
        $categorie = $this->route('categorie');

        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')
                    ->where('etablissement_id', $categorie->etablissement_id)
                    ->ignore($categorie),
            ],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer'],
            'statut' => ['sometimes', Rule::enum(StatutCategorie::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Le slug doit être en kebab-case (lettres minuscules, chiffres, tirets).',
            'slug.unique' => 'Ce slug est déjà utilisé par une autre catégorie de cet établissement.',
        ];
    }
}
