<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatutCategorie;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategorieRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Http\Resources\CategorieResource;
use App\Models\Categorie;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CategorieController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Categorie::class);

        $categories = Categorie::query()->orderBy('ordre')->orderBy('nom')->get();

        return CategorieResource::collection($categories);
    }

    public function store(StoreCategorieRequest $request): JsonResponse
    {
        $categorie = Categorie::create($request->validated());

        return (new CategorieResource($categorie))->response()->setStatusCode(201);
    }

    public function update(UpdateCategorieRequest $request, Categorie $categorie): CategorieResource
    {
        $categorie->update($request->validated());

        return new CategorieResource($categorie);
    }

    /**
     * N'efface jamais la ligne : une suppression réelle orphelinerait
     * silencieusement tous les produits de la catégorie, sans retour
     * possible pour le commerçant. Archiver (statut inactif) est la seule
     * forme de suppression exposée ici — cohérent avec Produit::destroy().
     */
    public function destroy(Categorie $categorie): JsonResponse
    {
        Gate::authorize('delete', $categorie);

        $categorie->update(['statut' => StatutCategorie::Inactif]);

        return response()->json(null, 204);
    }
}
