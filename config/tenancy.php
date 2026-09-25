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

];
