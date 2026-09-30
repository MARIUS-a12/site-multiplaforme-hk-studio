<?php

namespace App\Http\Controllers\Api\Vitrine;

use App\Http\Controllers\Controller;
use App\Http\Resources\Vitrine\EtablissementVitrineResource;
use App\Support\Tenancy\ContexteEtablissement;
use App\Support\Vitrine\CacheVitrine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class VitrineEtablissementController extends Controller
{
    /**
     * Met en cache le tableau déjà résolu, pas le modèle Etablissement —
     * voir la docblock de VitrineProduitController pour la raison
     * (cache.serializable_classes = false empêche unserialize() de
     * reconstruire un modèle Eloquent).
     */
    public function show(): JsonResponse
    {
        $contexte = app(ContexteEtablissement::class);
        $cle = CacheVitrine::cle($contexte->id(), 'etablissement');

        $donnees = Cache::remember(
            $cle,
            60,
            fn () => (new EtablissementVitrineResource($contexte->obtenir()))->response()->getData(true),
        );

        return response()->json($donnees);
    }
}
