<?php

namespace App\Services\Activation;

use App\Models\CodeActivation;
use App\Models\Etablissement;
use App\Models\JournalAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * SEUL endroit qui décide si un (email, code) est valide pour CET
 * établissement, et qui consomme le code. Un code inexistant, expiré, déjà
 * utilisé, ou d'un autre établissement produisent EXACTEMENT le même refus —
 * jamais lequel des quatre cas s'applique, ce serait un outil d'énumération
 * des comptes (voir echouer()).
 */
class ActiverCompte
{
    public function executer(Etablissement $etablissement, string $email, string $code, string $nouveauMotDePasse): User
    {
        $codeNormalise = Str::upper(trim($code));

        $candidats = CodeActivation::where('etablissement_id', $etablissement->id)
            ->whereNull('utilise_le')
            ->where('expire_le', '>', now())
            ->whereHas('utilisateur', fn ($requete) => $requete->where('email', $email))
            ->with('utilisateur')
            ->get();

        $ligne = $candidats->first(fn (CodeActivation $candidat) => Hash::check($codeNormalise, $candidat->code));

        if ($ligne === null) {
            $this->echouer();
        }

        return DB::transaction(function () use ($ligne, $nouveauMotDePasse) {
            $utilisateur = $ligne->utilisateur;

            $utilisateur->update([
                'password' => $nouveauMotDePasse,
                'mot_de_passe_defini' => true,
            ]);

            $ligne->update(['utilise_le' => now()]);

            JournalAudit::create([
                'utilisateur_id' => $utilisateur->id,
                'action' => 'activation_reussie',
                'details' => ['membre_id' => $utilisateur->id],
                'adresse_ip' => request()?->ip(),
            ]);

            return $utilisateur;
        });
    }

    private function echouer(): never
    {
        throw ValidationException::withMessages([
            'code' => ["Ce code n'est pas valide ou a expiré."],
        ]);
    }
}
