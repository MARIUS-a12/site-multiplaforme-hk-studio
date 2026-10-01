<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigurerPaiementEtablissementRequest;
use App\Http\Resources\PaiementEtablissementResource;
use App\Models\Etablissement;
use App\Services\Paiements\ConfigurerPaiementEtablissement;
use App\Services\Paiements\SupprimerConfigurationPaiement;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * UN seul contrôleur, UN seul couple de services pour les deux chemins
 * d'accès à la configuration de paiement (voir Étape 6C-1, même principe que
 * IdentiteEtablissementController) :
 * - commerçant, sur son propre établissement : routes "/parametres/paiement*",
 *   sans paramètre de route — exige "gerer_parametres" en LECTURE comme en
 *   ÉCRITURE (contrairement à l'identité, dont la lecture est ouverte à tout
 *   membre) : des identifiants de paiement, même masqués, ne regardent que
 *   qui a le droit de les gérer.
 * - super-admin, sur n'importe lequel : routes "/etablissements/{etablissement}/paiement*",
 *   pour pouvoir dépanner un commerçant.
 */
class PaiementEtablissementController extends Controller
{
    public function __construct(
        private readonly ConfigurerPaiementEtablissement $configurer,
        private readonly SupprimerConfigurationPaiement $supprimer,
    ) {}

    public function show(?Etablissement $etablissement = null): PaiementEtablissementResource
    {
        return new PaiementEtablissementResource($this->resoudreCible($etablissement, ecriture: false));
    }

    public function update(ConfigurerPaiementEtablissementRequest $request, ?Etablissement $etablissement = null): PaiementEtablissementResource
    {
        $cible = $this->resoudreCible($etablissement, ecriture: true);

        $misAJour = $this->configurer->executer(
            etablissement: $cible,
            siteId: $request->validated('cinetpay_site_id'),
            cleApi: $request->validated('cinetpay_cle_api'),
            secret: $request->validated('cinetpay_secret'),
            utilisateur: $request->user(),
            adresseIp: $request->ip(),
        );

        return new PaiementEtablissementResource($misAJour);
    }

    public function destroy(Request $request, ?Etablissement $etablissement = null): JsonResponse
    {
        $cible = $this->resoudreCible($etablissement, ecriture: true);
        $this->supprimer->executer($cible, $request->user(), $request->ip());

        return response()->json(null, 204);
    }

    private function resoudreCible(?Etablissement $etablissement, bool $ecriture): Etablissement
    {
        if ($etablissement !== null) {
            Gate::authorize($ecriture ? 'update' : 'view', $etablissement);

            return $etablissement;
        }

        abort_unless(auth()->user()->peut('gerer_parametres'), 403);

        return app(ContexteEtablissement::class)->obtenir();
    }
}
