<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hôte du super-admin
    |--------------------------------------------------------------------------
    |
    | Exclu de la résolution d'établissement (ResoudreEtablissement) et de la
    | connexion établissement-scopée (SessionController) : le super-admin n'a
    | pas d'établissement, il a son propre hôte.
    |
    */

    'hote_super_admin' => env('HOTE_SUPER_ADMIN', 'admin.localhost'),

    /*
    |--------------------------------------------------------------------------
    | Suffixe des sous-domaines créés par le super-admin
    |--------------------------------------------------------------------------
    |
    | Le formulaire de création d'établissement ne demande qu'un
    | sous-domaine (ex. "boutique-test") : ce suffixe complète le hôte réel
    | du Domaine créé (ex. "boutique-test.localhost"). En développement,
    | ".localhost" ; en production, le domaine réel de la plateforme.
    |
    */

    'suffixe_domaine' => env('SUFFIXE_DOMAINE', '.localhost'),

];
