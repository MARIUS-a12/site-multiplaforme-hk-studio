<?php

namespace App\Http\Requests;

use App\Enums\StatutProduit;
use App\Models\Categorie;
use App\Models\Produit;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Produit::class);
    }

    /**
     * slug est déduit de nom quand il est absent, avant toute validation,
     * pour que la règle d'unicité et le format kebab-case s'appliquent aussi
     * à la valeur générée, pas seulement à celle éventuellement fournie.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('nom')) {
            $this->merge(['slug' => Str::slug($this->input('nom'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('produits', 'slug')->where('etablissement_id', app(ContexteEtablissement::class)->id()),
            ],
            'description' => ['nullable', 'string'],
            'reference' => ['nullable', 'string', 'max:255'],
            'prix' => ['required', 'integer', 'min:0'],
            'prix_barre' => ['nullable', 'integer', 'min:0', 'gt:prix'],
            'categorie_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($value !== null && ! Categorie::whereKey($value)->exists()) {
                        $fail('La catégorie sélectionnée n\'appartient pas à cet établissement.');
                    }
                },
            ],
            'quantite_stock' => ['nullable', 'integer', 'min:0'],
            // Pertinent seulement en mode interrupteur (restaurant) ; en
            // mode compte, la disponibilité se déduit du stock (voir
            // Produit::estDisponibleEnQuantite()). Accepté dans les deux cas
            // plutôt que de le refuser selon le mode : ce serait dupliquer
            // côté validation une règle qui vit déjà dans le modèle.
            'disponible' => ['nullable', 'boolean'],
            'statut' => ['nullable', Rule::enum(StatutProduit::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'prix.integer' => 'Le prix doit être un nombre entier (XOF), sans décimale.',
            'prix_barre.integer' => 'Le prix barré doit être un nombre entier (XOF), sans décimale.',
            'prix_barre.gt' => 'Le prix barré doit être supérieur au prix.',
            'slug.regex' => 'Le slug doit être en kebab-case (lettres minuscules, chiffres, tirets).',
            'slug.unique' => 'Ce slug est déjà utilisé par un autre produit de cet établissement.',
        ];
    }
}
