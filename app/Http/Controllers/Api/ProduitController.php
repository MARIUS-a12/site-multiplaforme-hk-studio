<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Stub minimal : cette étape porte sur l'authentification et les
 * permissions, pas sur le CRUD catalogue. show()/update()/destroy() vérifient
 * la policy et renvoient une réponse triviale — un id d'un autre
 * établissement ne les atteint jamais : le binding de route filtre déjà par
 * le scope tenant (AppartientAEtablissement), donc 404 avant toute
 * autorisation.
 */
class ProduitController extends Controller
{
    public function show(Produit $produit): JsonResponse
    {
        Gate::authorize('view', $produit);

        return response()->json(['id' => $produit->id, 'nom' => $produit->nom]);
    }

    public function update(Request $request, Produit $produit): JsonResponse
    {
        Gate::authorize('update', $produit);

        return response()->json(['id' => $produit->id, 'nom' => $produit->nom]);
    }

    public function destroy(Produit $produit): JsonResponse
    {
        Gate::authorize('delete', $produit);

        return response()->json(null, 204);
    }
}
