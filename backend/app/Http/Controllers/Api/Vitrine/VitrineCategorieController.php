<?php

namespace App\Http\Controllers\Api\Vitrine;

use App\Enums\StatutCategorie;
use App\Enums\StatutProduit;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vitrine\CategorieVitrineResource;
use App\Models\Categorie;
use App\Support\Tenancy\ContexteEtablissement;
use App\Support\Vitrine\CacheVitrine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class VitrineCategorieController extends Controller
{
    /**
     * Seulement les catégories actives contenant au moins un produit publié
     * — une catégorie vide (ou dont tous les produits sont en brouillon)
     * n'a aucune raison d'apparaître dans les filtres de la vitrine.
     *
     * Met en cache le tableau déjà résolu, pas la collection de modèles —
     * voir la docblock de VitrineProduitController pour la raison
     * (cache.serializable_classes = false empêche unserialize() de
     * reconstruire un modèle Eloquent).
     */
    public function index(): JsonResponse
    {
        $etablissementId = app(ContexteEtablissement::class)->id();
        $cle = CacheVitrine::cle($etablissementId, 'categories');

        $donnees = Cache::remember($cle, 60, function () {
            $categories = Categorie::query()
                ->where('statut', StatutCategorie::Actif)
                ->whereHas('produits', fn ($requete) => $requete->where('statut', StatutProduit::Publie))
                ->orderBy('ordre')
                ->get();

            return CategorieVitrineResource::collection($categories)->response()->getData(true);
        });

        return response()->json($donnees);
    }
}
