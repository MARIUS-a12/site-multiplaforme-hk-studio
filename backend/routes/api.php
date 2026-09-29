<?php

use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\MediaProduitController;
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

    // Pas de "resoudre.etablissement" ici : réservées au super-admin (voir
    // EtablissementPolicy), qui n'a justement pas d'établissement courant.
    // "sous-domaine-disponible" avant "{etablissement}" : sinon ce dernier
    // capturerait le chemin littéral comme un id de route.
    Route::get('/etablissements/sous-domaine-disponible', [EtablissementController::class, 'verifierSousDomaine']);
    Route::get('/etablissements', [EtablissementController::class, 'index']);
    Route::post('/etablissements', [EtablissementController::class, 'store']);
    Route::get('/etablissements/{etablissement}', [EtablissementController::class, 'show']);
    Route::post('/etablissements/{etablissement}/suspendre', [EtablissementController::class, 'suspendre']);
    Route::post('/etablissements/{etablissement}/reactiver', [EtablissementController::class, 'reactiver']);

    Route::middleware('resoudre.etablissement')->group(function () {
        Route::get('/produits', [ProduitController::class, 'index']);
        Route::post('/produits', [ProduitController::class, 'store']);
        Route::get('/produits/{produit}', [ProduitController::class, 'show']);
        Route::put('/produits/{produit}', [ProduitController::class, 'update']);
        Route::delete('/produits/{produit}', [ProduitController::class, 'destroy']);

        Route::put('/produits/{produit}/medias/ordre', [MediaProduitController::class, 'reordonner']);
        Route::post('/produits/{produit}/medias', [MediaProduitController::class, 'store']);
        Route::delete('/produits/{produit}/medias/{media}', [MediaProduitController::class, 'destroy']);

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
