<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ContexteEtablissement::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Le super-admin contourne toutes les policies. Explicite ici, dans
        // un seul endroit à auditer — jamais par un `return true` dissimulé
        // dans telle ou telle policy.
        Gate::before(fn (User $user, string $ability) => $user->estSuperAdmin() ? true : null);
    }
}
