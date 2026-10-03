<?php

namespace App\Http\Requests\Equipe;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangerStatutMembreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('changerStatut', $this->route('membre'));
    }

    public function rules(): array
    {
        return [
            'statut' => ['required', Rule::in(['actif', 'suspendu'])],
        ];
    }
}
