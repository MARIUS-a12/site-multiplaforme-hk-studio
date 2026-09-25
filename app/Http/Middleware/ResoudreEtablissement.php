<?php

namespace App\Http\Middleware;

use App\Models\Domaine;
use App\Support\Tenancy\ContexteEtablissement;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResoudreEtablissement
{
    public function __construct(
        protected ContexteEtablissement $contexte,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $hote = $request->getHost();

        // Le super-admin a son propre hôte, sans établissement : exclu de la
        // résolution, le contexte reste indéfini pour la suite de la requête.
        if ($hote === config('tenancy.hote_super_admin')) {
            return $next($request);
        }

        $etablissement = Domaine::pourHote($hote);

        if ($etablissement === null || ! $etablissement->estActif()) {
            throw new NotFoundHttpException("Aucun établissement actif pour le domaine [{$hote}].");
        }

        $this->contexte->definir($etablissement);

        return $next($request);
    }
}
