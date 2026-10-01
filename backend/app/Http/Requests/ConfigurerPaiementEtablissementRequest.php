<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Les trois identifiants sont "required" ensemble : aucune configuration
 * partielle possible (voir Étape 6C-1).
 */
class ConfigurerPaiementEtablissementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cinetpay_site_id' => ['required', 'string', 'max:255'],
            'cinetpay_cle_api' => ['required', 'string'],
            'cinetpay_secret' => ['required', 'string'],
        ];
    }
}
