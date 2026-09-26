<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pas de modèle Eloquent derrière "statistiques" : pas de policy possible,
 * juste `peut()` directement. Stub minimal, voir ProduitController.
 */
class StatistiquesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->peut('voir_statistiques'), 403);

        return response()->json(['ok' => true]);
    }
}
