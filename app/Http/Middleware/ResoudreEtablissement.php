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

        $domaine = Domaine::with('etablissement')->where('hote', $hote)->first();

        if (! $domaine || ! $domaine->etablissement || ! $domaine->etablissement->estActif()) {
            throw new NotFoundHttpException("Aucun établissement actif pour le domaine [{$hote}].");
        }

        $this->contexte->definir($domaine->etablissement);

        return $next($request);
    }
}
