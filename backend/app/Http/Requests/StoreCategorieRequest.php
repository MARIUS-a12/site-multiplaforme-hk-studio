<?php

namespace App\Http\Requests;

use App\Enums\StatutCategorie;
use App\Models\Categorie;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Categorie::class);
    }

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
                Rule::unique('categories', 'slug')->where('etablissement_id', app(ContexteEtablissement::class)->id()),
            ],
            'description' => ['nullable', 'string'],
            'ordre' => ['nullable', 'integer'],
            'statut' => ['nullable', Rule::enum(StatutCategorie::class)],
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
