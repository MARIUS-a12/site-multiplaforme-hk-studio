<?php

namespace App\Services\Equipe;

use App\Models\EtablissementUtilisateur;
use App\Models\JournalAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Génère un nouveau mot de passe pour un membre, affiché une seule fois à
 * l'administrateur qui déclenche l'action (jamais relisible ensuite — même
 * contrat que CreerMembreEquipe) et invalide toutes ses sessions en cours.
 */
class ReinitialiserMotDePasseMembreEquipe
{
    public function executer(EtablissementUtilisateur $membre, User $acteur, ?string $adresseIp): string
    {
        $membre->loadMissing('utilisateur');

        $motDePasseGenere = Str::password(14);

        $membre->utilisateur->update(['password' => $motDePasseGenere]);

        DB::table('sessions')->where('user_id', $membre->utilisateur_id)->delete();

        JournalAudit::create([
            'utilisateur_id' => $acteur->id,
            'action' => 'equipe_mot_de_passe_reinitialise',
            'details' => ['membre_id' => $membre->utilisateur_id],
            'adresse_ip' => $adresseIp,
        ]);

        return $motDePasseGenere;
    }
}
