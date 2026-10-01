<?php

namespace App\Rules;

use App\Support\Telephone\NormaliseurTelephone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NumeroTelephoneIvoirien implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || NormaliseurTelephone::normaliser($value) === null) {
            $fail('Le numéro de téléphone n\'est pas valide. Formats acceptés : 0X XX XX XX XX ou +225 suivi des 10 chiffres.');
        }
    }
}
