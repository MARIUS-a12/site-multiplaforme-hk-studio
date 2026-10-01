<?php

namespace App\Http\Controllers\Api\Vitrine;

use App\Enums\StatutZoneLivraison;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vitrine\ZoneLivraisonVitrineResource;
use App\Models\ZoneLivraison;
use Illuminate\Http\JsonResponse;

class VitrineZoneLivraisonController extends Controller
{
    public function index(): JsonResponse
    {
        $zones = ZoneLivraison::where('statut', StatutZoneLivraison::Actif)
            ->orderBy('nom')
            ->get();

        return response()->json(['data' => ZoneLivraisonVitrineResource::collection($zones)]);
    }
}
