<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Détecte toute divergence entre users.etablissement_id (colonne
 * dénormalisée) et la ligne etablissement_utilisateurs (source de vérité
 * du rôle/statut) — les deux doivent TOUJOURS dire la même chose depuis
 * CreerRattachementUtilisateur, seul point qui les écrit ensemble. Ce
 * contrôle ne corrige rien : il signale, pour investigation manuelle,
 * destiné à tourner à la main (y compris sur la base de production), pas en
 * tâche planifiée.
 */
class VerifierCoherenceRattachements extends Command
{
    protected $signature = 'utilisateurs:verifier-rattachements';

    protected $description = "Signale les utilisateurs dont users.etablissement_id et etablissement_utilisateurs divergent, ou dont l'un des deux manque";

    public function handle(): int
    {
        $lignes = DB::table('users as u')
            ->leftJoin('etablissement_utilisateurs as eu', 'eu.utilisateur_id', '=', 'u.id')
            ->select('u.id', 'u.email', 'u.etablissement_id as colonne', 'eu.etablissement_id as pivot')
            ->orderBy('u.id')
            ->get();

        $normaliser = fn ($valeur) => $valeur === null ? null : (int) $valeur;

        $divergences = $lignes->filter(
            fn ($ligne) => $normaliser($ligne->colonne) !== $normaliser($ligne->pivot)
        );

        if ($divergences->isEmpty()) {
            $this->info('Aucune divergence : users.etablissement_id et etablissement_utilisateurs sont cohérents pour '.$lignes->count().' utilisateur(s).');

            return self::SUCCESS;
        }

        $this->error($divergences->count().' divergence(s) détectée(s) :');

        foreach ($divergences as $ligne) {
            $colonne = $ligne->colonne ?? 'absente';
            $pivot = $ligne->pivot ?? 'absent';

            $this->line("  #{$ligne->id} ({$ligne->email}) — users.etablissement_id={$colonne}, etablissement_utilisateurs.etablissement_id={$pivot}");
        }

        return self::FAILURE;
    }
}
