<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Publié (au lieu du défaut du framework) pour Sanctum en mode SPA :
    | l'authentification par cookie de session exige `supports_credentials`
    | à true, ce qui interdit `allowed_origins => ['*']` (les navigateurs
    | refusent la combinaison credentials + wildcard). La liste vient donc de
    | CORS_ALLOWED_ORIGINS, à tenir à jour avec le(s) domaine(s) du frontend —
    | en pratique les mêmes hôtes que SANCTUM_STATEFUL_DOMAINS, avec le schéma
    | en plus (http(s)://hote[:port] contre hote[:port]).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:5173,http://*.localhost:5173,http://localhost:8000,http://*.localhost:8000',
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
