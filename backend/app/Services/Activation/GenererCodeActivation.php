<?php

namespace App\Services\Activation;

use App\Models\CodeActivation;
use App\Models\Etablissement;
use App\Models\JournalAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * SEUL point qui génère un code d'activation — appelé par CreerMembreEquipe
 * (nouveau membre) et GenererNouveauCodeAccesMembre ("mot de passe oublié").
 * Le code en clair ne transite QUE par la valeur de retour, jamais stocké
 * ailleurs qu'ici (haché) ni journalisé.
 */
class GenererCodeActivation
{
    /**
     * Sans 0, O, 1, I, L : le code sera lu à voix haute ou dicté par
     * téléphone, ces caractères se confondent trop facilement à l'oreille
     * ou à l'écran.
     */
    private const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    private const LONGUEUR = 8;

    private const VALIDITE_HEURES = 48;

    public function executer(User $utilisateur, Etablissement $etablissement, User $acteur, ?string $adresseIp): string
    {
        return DB::transaction(function () use ($utilisateur, $etablissement, $acteur, $adresseIp) {
            // Un seul code actif par utilisateur : tout code encore valide
            // est expiré immédiatement plutôt que supprimé — l'historique
            // des codes générés reste lisible.
            CodeActivation::where('utilisateur_id', $utilisateur->id)
                ->whereNull('utilise_le')
                ->where('expire_le', '>', now())
                ->update(['expire_le' => now()]);

            $code = $this->genererCode();

            CodeActivation::create([
                'etablissement_id' => $etablissement->id,
                'utilisateur_id' => $utilisateur->id,
                'code' => Hash::make($code),
                'expire_le' => now()->addHours(self::VALIDITE_HEURES),
                'cree_par' => $acteur->id,
            ]);

            JournalAudit::create([
                'utilisateur_id' => $acteur->id,
                'action' => 'code_activation_genere',
                'details' => ['membre_id' => $utilisateur->id, 'email' => $utilisateur->email],
                'adresse_ip' => $adresseIp,
            ]);

            return $code;
        });
    }

    private function genererCode(): string
    {
        $alphabet = self::ALPHABET;
        $dernierIndex = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < self::LONGUEUR; $i++) {
            $code .= $alphabet[random_int(0, $dernierIndex)];
        }

        return $code;
    }
}
