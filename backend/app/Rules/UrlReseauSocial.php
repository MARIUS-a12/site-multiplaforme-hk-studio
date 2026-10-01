<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Un lien "Facebook" qui pointerait ailleurs que facebook.com serait une
 * porte d'hameçonnage hébergée sur la plateforme : l'hôte doit correspondre
 * au réseau annoncé, pas seulement avoir la forme d'une URL. Accepte les
 * sous-domaines (www.facebook.com, m.facebook.com...).
 */
class UrlReseauSocial implements ValidationRule
{
    public function __construct(
        private readonly string $hoteAttendu,
        private readonly string $nomReseau,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $hote = parse_url($value, PHP_URL_HOST);

        if ($hote === null || $hote === false) {
            $fail("Ce lien n'est pas une adresse valide.");

            return;
        }

        $hote = strtolower($hote);

        if ($hote !== $this->hoteAttendu && ! str_ends_with($hote, '.'.$this->hoteAttendu)) {
            $fail("Ce lien ne pointe pas vers {$this->nomReseau} ({$this->hoteAttendu}).");
        }
    }
}
