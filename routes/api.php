<?php

use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\ParametresController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\StatistiquesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Pas de "resoudre.etablissement" ici : SessionController résout lui-même le
// domaine, SANS lever sur un établissement inactif (voir sa docblock) — la
// version stricte du middleware romprait l'uniformité des 4 échecs de
// connexion (voir Étape 1, exigence n°7).
Route::post('/connexion', [SessionController::class, 'store'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deconnexion', [SessionController::class, 'destroy']);
    Route::get('/moi', [SessionController::class, 'moi'])->middleware('resoudre.etablissement');

    Route::middleware('resoudre.etablissement')->group(function () {
        Route::get('/produits', [ProduitController::class, 'index']);
        Route::post('/produits', [ProduitController::class, 'store']);
        Route::get('/produits/{produit}', [ProduitController::class, 'show']);
        Route::put('/produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

        Route::get('/categories', [CategorieController::class, 'index']);
        Route::post('/categories', [CategorieController::class, 'store']);
        Route::put('/categories/{categorie}', [CategorieController::class, 'update']);
        Route::delete('/categories/{categorie}', [CategorieController::class, 'destroy']);

        Route::get('/commandes', [CommandeController::class, 'index']);

        Route::get('/parametres', [ParametresController::class, 'index']);
        Route::get('/statistiques', [StatistiquesController::class, 'index']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
