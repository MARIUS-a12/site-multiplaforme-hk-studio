<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatutProduit;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProduitRequest;
use App\Http\Requests\UpdateProduitRequest;
use App\Http\Resources\ProduitResource;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProduitController extends Controller
{
    /**
     * `resoudre.etablissement` a déjà posé le contexte : le scope global
     * AppartientAEtablissement filtre donc cette requête sans qu'on ait
     * besoin d'un ->where('etablissement_id', ...) manuel ici.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Produit::class);

        $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'categorie_id' => ['nullable', 'integer'],
            'statut' => ['nullable', Rule::enum(StatutProduit::class)],
            'tri' => ['nullable', 'in:nom,date'],
            'direction' => ['nullable', 'in:asc,desc'],
            'par_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $colonneDeTri = $request->string('tri', 'date')->value() === 'nom' ? 'nom' : 'created_at';
        $direction = $request->string('direction', 'desc')->value() === 'asc' ? 'asc' : 'desc';

        $produits = Produit::query()
            ->when($request->filled('recherche'), fn ($requete) => $requete->where('nom', 'like', '%'.$request->string('recherche').'%'))
            ->when($request->filled('categorie_id'), fn ($requete) => $requete->where('categorie_id', $request->integer('categorie_id')))
            ->when($request->filled('statut'), fn ($requete) => $requete->where('statut', $request->string('statut')->value()))
            ->orderBy($colonneDeTri, $direction)
            ->paginate($request->integer('par_page', 20));

        return ProduitResource::collection($produits);
    }

    public function store(StoreProduitRequest $request): JsonResponse
    {
        $produit = Produit::create($request->validated());

        return (new ProduitResource($produit))->response()->setStatusCode(201);
    }

    public function show(Produit $produit): ProduitResource
    {
        Gate::authorize('view', $produit);

        return new ProduitResource($produit);
    }

    public function update(UpdateProduitRequest $request, Produit $produit): ProduitResource
    {
        $produit->update($request->validated());

        return new ProduitResource($produit);
    }

    /**
     * N'efface jamais la ligne : l'historique (lignes de commande,
     * réservations) en dépend. Archiver est la seule forme de suppression
     * exposée par cette API (voir Étape 2, règle n°1).
     */
    public function destroy(Produit $produit): JsonResponse
    {
        Gate::authorize('delete', $produit);

        $produit->update(['statut' => StatutProduit::Archive]);

        return response()->json(null, 204);
    }
}
