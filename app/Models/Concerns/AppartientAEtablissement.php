<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\Eloquent\Builder;

trait AppartientAEtablissement
{
    public static function bootAppartientAEtablissement(): void
    {
        static::addGlobalScope(new ScopeEtablissement);

        static::creating(function ($model) {
            if ($model->etablissement_id !== null) {
                return;
            }

            $contexte = app(ContexteEtablissement::class);

            if ($contexte->estDefini()) {
                $model->etablissement_id = $contexte->id();
            }
        });
    }

    public static function pourTousEtablissements(): Builder
    {
        return static::withoutGlobalScope(ScopeEtablissement::class);
    }
}
