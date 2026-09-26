<?php

use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Support\Facades\Route;

Route::middleware('resoudre.etablissement')->group(function () {
    Route::get('/', function (ContexteEtablissement $contexte) {
        $etablissement = $contexte->obtenir();

        return "{$etablissement->nom} ({$etablissement->type})";
    });
});
