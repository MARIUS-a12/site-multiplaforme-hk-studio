<?php

namespace App\Services\Equipe;

use App\Models\Etablissement;
use App\Models\JournalAudit;
use App\Models\Role;
use App\Models\User;
use App\Services\Utilisateurs\CreerRattachementUtilisateur;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Crée TOUJOURS une nouvelle ligne User, jamais réutilisée d'un autre
 * établissement : deux comptes du même email dans deux établissements
 * différents sont deux comptes distincts (voir migration
 * "preparer_utilisateurs_pour_equipe", qui retire l'unicité globale de
 * users.email). L'unicité "au sein de CET établissement" est vérifiée en
 * amont par StoreMembreEquipeRequest.
 */
class CreerMembreEquipe
{
    public function __construct(
        private readonly CreerRattachementUtilisateur $creerRattachement,
    ) {}

    public function executer(
        Etablissement $etablissement,
        string $nom,
        string $email,
        int $roleId,
        User $acteur,
        ?string $adresseIp,
    ): ResultatCreationMembreEquipe {
        return DB::transaction(function () use ($etablissement, $nom, $email, $roleId, $acteur, $adresseIp) {
            // Un mot de passe aléatoire, jamais choisi par l'administrateur
            // qui crée le compte — même procédé qu'à la création d'un
            // établissement (voir CreerEtablissement).
            $motDePasseGenere = Str::password(14);

            $utilisateur = User::create([
                'name' => $nom,
                'email' => $email,
                'password' => $motDePasseGenere,
            ]);

            $membre = $this->creerRattachement->executer($utilisateur, $etablissement, $roleId);

            JournalAudit::create([
                'utilisateur_id' => $acteur->id,
                'action' => 'equipe_membre_cree',
                'details' => [
                    'membre_id' => $utilisateur->id,
                    'email' => $email,
                    'role' => Role::whereKey($roleId)->value('nom'),
                ],
                'adresse_ip' => $adresseIp,
            ]);

            return new ResultatCreationMembreEquipe($membre, $motDePasseGenere);
        });
    }
}
