<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EtablissementResource;
use App\Models\Etablissement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

/**
 * Réservé au super-admin (voir EtablissementPolicy) : liste brute des
 * établissements de la plateforme. Volontairement hors du groupe
 * resoudre.etablissement — cette route n'a justement pas d'établissement
 * courant, et Etablissement n'est pas un modèle tenant-scopé.
 */
class EtablissementController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Etablissement::class);

        $etablissements = Etablissement::query()->with('domaines')->orderBy('nom')->get();

        return EtablissementResource::collection($etablissements);
    }
}
