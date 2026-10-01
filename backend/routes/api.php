<?php

use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\CommandeController;
use App\Http\Controllers\Api\CompteController;
use App\Http\Controllers\Api\EtablissementController;
use App\Http\Controllers\Api\IdentiteEtablissementController;
use App\Http\Controllers\Api\MediaProduitController;
use App\Http\Controllers\Api\ParametresController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\StatistiquesController;
use App\Http\Controllers\Api\Vitrine\VitrineCategorieController;
use App\Http\Controllers\Api\Vitrine\VitrineCommandeController;
use App\Http\Controllers\Api\Vitrine\VitrineEtablissementController;
use App\Http\Controllers\Api\Vitrine\VitrinePanierController;
use App\Http\Controllers\Api\Vitrine\VitrineProduitController;
use App\Http\Controllers\Api\Vitrine\VitrineZoneLivraisonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Pas de "resoudre.etablissement" ici : SessionController résout lui-même le
// domaine, SANS lever sur un établissement inactif (voir sa docblock) — la
// version stricte du middleware romprait l'uniformité des 4 échecs de
// connexion (voir Étape 1, exigence n°7).
Route::post('/connexion', [SessionController::class, 'store'])->middleware('throttle:5,1');

// Vitrine publique : ni "auth:sanctum" ni "resoudre.etablissement" strict au
// sens d'exiger une session — un visiteur anonyme doit pouvoir tout
// consulter. Seul "resoudre.etablissement" s'applique (isolation par
// sous-domaine, 404 si l'établissement est inactif ou inconnu).
Route::middleware('resoudre.etablissement')->prefix('vitrine')->group(function () {
    Route::get('/produits', [VitrineProduitController::class, 'index']);
    Route::get('/produits/{id}', [VitrineProduitController::class, 'show']);
    Route::post('/produits/{id}/lien-whatsapp', [VitrineProduitController::class, 'lienWhatsapp']);
    Route::get('/categories', [VitrineCategorieController::class, 'index']);
    Route::get('/etablissement', [VitrineEtablissementController::class, 'show']);

    // Étape 6B — panier et commande : le navigateur n'envoie que des
    // identifiants et des quantités, jamais un prix (voir
    // CreerCommandeVitrineRequest). "commandes/{numero}" avant tout autre
    // verbe sur "commandes" n'a pas d'ambiguïté possible ici (un seul GET).
    Route::post('/panier/verifier', [VitrinePanierController::class, 'verifier']);
    Route::get('/zones-livraison', [VitrineZoneLivraisonController::class, 'index']);
    Route::post('/commandes', [VitrineCommandeController::class, 'store']);
    Route::get('/commandes/{numero}', [VitrineCommandeController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/deconnexion', [SessionController::class, 'destroy']);
    Route::get('/moi', [SessionController::class, 'moi'])->middleware('resoudre.etablissement');

    // "Mon compte" — Étape 7 : accessible à tout utilisateur authentifié,
    // super-admin compris, donc volontairement hors de "resoudre.etablissement".
    Route::patch('/compte/mot-de-passe', [CompteController::class, 'mettreAJourMotDePasse'])->middleware('throttle:5,60');
    Route::patch('/compte/profil', [CompteController::class, 'mettreAJourProfil']);

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
    Route::delete('/etablissements/{etablissement}', [EtablissementController::class, 'destroy']);

    // Étape 6A ter — identité (chemin super-admin, n'importe quel
    // établissement) : MÊME contrôleur, MÊME service que le chemin
    // commerçant ci-dessous, voir IdentiteEtablissementController.
    Route::get('/etablissements/{etablissement}/identite', [IdentiteEtablissementController::class, 'show']);
    Route::patch('/etablissements/{etablissement}/identite', [IdentiteEtablissementController::class, 'update']);
    Route::post('/etablissements/{etablissement}/identite/logo', [IdentiteEtablissementController::class, 'uploaderLogo']);
    Route::delete('/etablissements/{etablissement}/identite/logo', [IdentiteEtablissementController::class, 'supprimerLogo']);

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

        // Étape 6A ter — identité (chemin commerçant, son propre
        // établissement, résolu par le sous-domaine) : pas de paramètre de
        // route, voir IdentiteEtablissementController::resoudreCible().
        Route::get('/parametres/etablissement', [IdentiteEtablissementController::class, 'show']);
        Route::patch('/parametres/etablissement', [IdentiteEtablissementController::class, 'update']);
        Route::post('/parametres/etablissement/logo', [IdentiteEtablissementController::class, 'uploaderLogo']);
        Route::delete('/parametres/etablissement/logo', [IdentiteEtablissementController::class, 'supprimerLogo']);

        Route::get('/statistiques', [StatistiquesController::class, 'index']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
