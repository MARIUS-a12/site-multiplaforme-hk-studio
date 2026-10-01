<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModifierMotDePasseRequest;
use App\Http\Requests\ModifierProfilRequest;
use App\Services\Utilisateurs\ModifierMotDePasseUtilisateur;
use App\Services\Utilisateurs\ModifierProfilUtilisateur;
use Illuminate\Http\JsonResponse;

/**
 * "Mon compte" — accessible à TOUT utilisateur authentifié, quel que soit
 * son rôle ou qu'il appartienne ou non à un établissement (super-admin
 * compris) : volontairement hors du groupe resoudre.etablissement, comme
 * /api/moi.
 */
class CompteController extends Controller
{
    public function mettreAJourMotDePasse(ModifierMotDePasseRequest $request, ModifierMotDePasseUtilisateur $service): JsonResponse
    {
        $service->executer(
            utilisateur: $request->user(),
            nouveauMotDePasse: $request->validated('nouveau_mot_de_passe'),
            idSessionActuelle: $request->session()->getId(),
            adresseIp: $request->ip(),
        );

        return response()->json(null, 204);
    }

    public function mettreAJourProfil(ModifierProfilRequest $request, ModifierProfilUtilisateur $service): JsonResponse
    {
        $utilisateur = $service->executer(
            utilisateur: $request->user(),
            nom: $request->validated('nom'),
            email: $request->validated('email'),
        );

        return response()->json([
            'utilisateur' => ['id' => $utilisateur->id, 'nom' => $utilisateur->name, 'email' => $utilisateur->email],
        ]);
    }
}
