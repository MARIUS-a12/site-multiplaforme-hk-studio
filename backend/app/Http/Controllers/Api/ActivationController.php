<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Activation\ActiverCompteRequest;
use App\Services\Activation\ActiverCompte;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Correctif Étape 10 — /admin/activation : un employé qui vient d'être créé
 * (voir CreerMembreEquipe) ou dont l'administrateur a généré un nouveau
 * code (voir GenererNouveauCodeAccesMembre) choisit lui-même son mot de
 * passe ici, une seule fois. Personne d'autre ne le connaît jamais.
 */
class ActivationController extends Controller
{
    public function activer(ActiverCompteRequest $request, ActiverCompte $service): JsonResponse
    {
        $utilisateur = $service->executer(
            etablissement: app(ContexteEtablissement::class)->obtenir(),
            email: $request->validated('email'),
            code: $request->validated('code'),
            nouveauMotDePasse: $request->validated('nouveau_mot_de_passe'),
        );

        // Même procédé que SessionController::store() : l'activation
        // réussie connecte directement, l'employé n'a pas à ressaisir ce
        // qu'il vient de choisir sur un second écran de connexion.
        Auth::guard('web')->login($utilisateur);
        $request->session()->regenerate();
        $utilisateur->update(['derniere_connexion_a' => now()]);

        return response()->json([
            'utilisateur' => [
                'id' => $utilisateur->id,
                'nom' => $utilisateur->name,
                'email' => $utilisateur->email,
            ],
        ]);
    }
}
