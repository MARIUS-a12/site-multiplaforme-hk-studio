<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CouleurAccentIllisibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MettreAJourIdentiteEtablissementRequest;
use App\Http\Requests\UploaderLogoEtablissementRequest;
use App\Http\Resources\IdentiteEtablissementResource;
use App\Models\Etablissement;
use App\Services\Etablissements\MettreAJourIdentiteEtablissement;
use App\Services\Etablissements\MettreAJourLogoEtablissement;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/**
 * UN seul contrôleur, UN seul service, UNE seule validation pour les deux
 * chemins d'accès à l'identité d'un établissement (voir Étape 6A ter) :
 * - commerçant, sur son propre établissement : routes "/parametres/etablissement*",
 *   sans paramètre de route — $etablissement vaut toujours null ici, résolu
 *   via le contexte de tenancy (sous-domaine).
 * - super-admin, sur n'importe lequel : routes "/etablissements/{etablissement}/identite*"
 *   — $etablissement est alors lié par la route.
 *
 * resoudreCible() est le SEUL endroit où ces deux chemins divergent
 * (autorisation) ; tout le reste de chaque méthode leur est commun.
 */
class IdentiteEtablissementController extends Controller
{
    public function __construct(
        private readonly MettreAJourIdentiteEtablissement $mettreAJourIdentite,
        private readonly MettreAJourLogoEtablissement $mettreAJourLogo,
    ) {}

    public function show(?Etablissement $etablissement = null): IdentiteEtablissementResource
    {
        return new IdentiteEtablissementResource($this->resoudreCible($etablissement, ecriture: false));
    }

    public function update(MettreAJourIdentiteEtablissementRequest $request, ?Etablissement $etablissement = null): JsonResponse
    {
        $cible = $this->resoudreCible($etablissement, ecriture: true);

        try {
            $mis_a_jour = $this->mettreAJourIdentite->executer($cible, $request->validated());
        } catch (CouleurAccentIllisibleException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'couleur_accent_suggeree' => $exception->couleurSuggeree,
            ], 422);
        }

        return (new IdentiteEtablissementResource($mis_a_jour))->response();
    }

    public function uploaderLogo(UploaderLogoEtablissementRequest $request, ?Etablissement $etablissement = null): JsonResponse
    {
        $cible = $this->resoudreCible($etablissement, ecriture: true);
        $mis_a_jour = $this->mettreAJourLogo->remplacer($cible, $request->file('logo'));

        return (new IdentiteEtablissementResource($mis_a_jour))->response()->setStatusCode(201);
    }

    public function supprimerLogo(?Etablissement $etablissement = null): JsonResponse
    {
        $cible = $this->resoudreCible($etablissement, ecriture: true);
        $this->mettreAJourLogo->supprimer($cible);

        return response()->json(null, 204);
    }

    private function resoudreCible(?Etablissement $etablissement, bool $ecriture): Etablissement
    {
        if ($etablissement !== null) {
            Gate::authorize($ecriture ? 'update' : 'view', $etablissement);

            return $etablissement;
        }

        if ($ecriture) {
            abort_unless(auth()->user()->peut('gerer_parametres'), 403);
        }

        return app(ContexteEtablissement::class)->obtenir();
    }
}
