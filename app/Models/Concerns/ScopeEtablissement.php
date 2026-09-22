<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

class ScopeEtablissement implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $contexte = app(ContexteEtablissement::class);

        if ($contexte->estDefini()) {
            $builder->where($model->qualifyColumn('etablissement_id'), $contexte->id());

            return;
        }

        if (app()->runningInConsole()) {
            return;
        }

        throw new RuntimeException(
            "Aucun établissement n'est défini dans le contexte de la requête : accès refusé à [{$model->getTable()}]."
        );
    }
}
