<?php

namespace App\Models\Concerns;

use App\Exceptions\EtablissementNonResoluException;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeEtablissement implements Scope
{
    /**
     * Filtre TOUJOURS, sans exception pour la console : un job de file ou
     * une commande artisan tournent eux aussi "en console", et un webhook de
     * paiement ou l'IA WhatsApp n'auront pas plus de contexte ambiant qu'un
     * artisan command. Les laisser passer sans filtre exposerait les données
     * de tous les établissements dès qu'un traitement en tâche de fond
     * interroge un modèle sans avoir résolu de tenant — silencieusement, en
     * plus, puisque ce genre de code ne s'exécute jamais devant quelqu'un qui
     * remarquerait l'anomalie.
     *
     * Un appelant qui a légitimement besoin d'ignorer la portée tenant (un
     * seeder, une commande artisan d'administration, un service qui opère
     * sur une instance de confiance déjà chargée par id) doit le dire
     * explicitement avec Model::pourTousEtablissements() — jamais compter
     * sur un passe-droit implicite lié au contexte d'exécution.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $contexte = app(ContexteEtablissement::class);

        if ($contexte->estDefini()) {
            $builder->where($model->qualifyColumn('etablissement_id'), $contexte->id());

            return;
        }

        throw new EtablissementNonResoluException(
            "Aucun établissement résolu pour ce domaine : accès refusé à [{$model->getTable()}]. ".
            'Un traitement (job, commande artisan, seeder) qui a légitimement besoin de toutes les données doit '.
            "appeler explicitement [{$model->getTable()}]::pourTousEtablissements()."
        );
    }
}
