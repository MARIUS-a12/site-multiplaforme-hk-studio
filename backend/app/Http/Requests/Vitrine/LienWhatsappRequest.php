<?php

namespace App\Http\Requests\Vitrine;

use Illuminate\Foundation\Http\FormRequest;

class LienWhatsappRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variante_id' => ['nullable', 'integer'],
        ];
    }
}
