<?php

namespace App\Support\Url;

/**
 * Toute URL de l'identité (réseaux sociaux, site web) est stockée en https,
 * jamais en http : appliqué avant validation (voir
 * MettreAJourIdentiteEtablissementRequest::prepareForValidation), pour que
 * la règle UrlReseauSocial juge déjà la forme finale.
 */
final class ForceHttps
{
    public static function appliquer(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return $url;
        }

        if (str_starts_with($url, 'http://')) {
            return 'https://'.substr($url, 7);
        }

        if (! str_starts_with($url, 'https://')) {
            return 'https://'.$url;
        }

        return $url;
    }
}
