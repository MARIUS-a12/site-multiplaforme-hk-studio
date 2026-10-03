<?php

namespace App\Services\Equipe;

use App\Models\EtablissementUtilisateur;
use App\Models\User;
use App\Services\Activation\GenererCodeActivation;
use Illuminate\Support\Facades\DB;

/**
 * Remplace l'ancienne réinitialisation de mot de passe (bouton "Générer un
 * nouveau code d'accès" de /admin/equipe) — l'administrateur ne voit jamais
 * le mot de passe du membre, ni avant ni après : il génère seulement un
 * nouveau code, que l'employé utilise pour EN CHOISIR un lui-même via
 * /admin/activation (voir ActiverCompte). Repasse mot_de_passe_defini à
 * faux et invalide les sessions en cours, exactement comme une
 * désactivation le ferait.
 */
class GenererNouveauCodeAccesMembre
{
    public function __construct(
        private readonly GenererCodeActivation $genererCodeActivation,
    ) {}

    public function executer(EtablissementUtilisateur $membre, User $acteur, ?string $adresseIp): string
    {
        return DB::transaction(function () use ($membre, $acteur, $adresseIp) {
            $membre->loadMissing(['utilisateur', 'etablissement']);

            $membre->utilisateur->update(['mot_de_passe_defini' => false]);

            DB::table('sessions')->where('user_id', $membre->utilisateur_id)->delete();

            return $this->genererCodeActivation->executer($membre->utilisateur, $membre->etablissement, $acteur, $adresseIp);
        });
    }
}
