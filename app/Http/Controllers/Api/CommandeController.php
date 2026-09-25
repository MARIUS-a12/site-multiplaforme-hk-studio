<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commande;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Stub minimal, voir ProduitController : cette étape porte sur les
 * permissions, pas sur le CRUD commandes.
 */
class CommandeController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Commande::class);

        return response()->json(['data' => []]);
    }
}
