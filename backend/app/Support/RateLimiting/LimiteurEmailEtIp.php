<?php

namespace App\Support\RateLimiting;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Limiteur combiné, partagé par /connexion et /activation : une IP seule
 * est un mauvais verrou dans ce contexte, les opérateurs mobiles ivoiriens
 * partagent une même adresse entre un grand nombre d'abonnés sans aucun
 * lien entre eux, qui se bloqueraient mutuellement. La vraie protection
 * contre le bourrage d'un compte précis est la limite par email (5/heure,
 * stricte) ; la limite par IP (30/heure, large) ne reste là que pour
 * arrêter un balayage automatisé, pas pour pénaliser un partage légitime
 * de sortie réseau.
 */
class LimiteurEmailEtIp
{
    private const TENTATIVES_PAR_EMAIL = 5;

    private const TENTATIVES_PAR_IP = 30;

    private const FENETRE_HEURES = 1;

    /**
     * $action identifie le limiteur dans les clés de cache et le journal
     * ("connexion", "activation") — jamais exposé au client.
     */
    public static function pour(string $action): Closure
    {
        return function (Request $request) use ($action) {
            $email = Str::lower(trim((string) $request->input('email')));

            return [
                Limit::perHour(self::TENTATIVES_PAR_EMAIL, self::FENETRE_HEURES)
                    ->by("{$action}:email:{$email}")
                    ->response(fn ($request, $headers) => self::reponseBloquee($request, $headers, $action, $email)),
                Limit::perHour(self::TENTATIVES_PAR_IP, self::FENETRE_HEURES)
                    ->by("{$action}:ip:{$request->ip()}")
                    ->response(fn ($request, $headers) => self::reponseBloquee($request, $headers, $action, $email)),
            ];
        };
    }

    private static function reponseBloquee(Request $request, array $headers, string $action, string $email): JsonResponse
    {
        $minutes = max(1, (int) ceil((int) ($headers['Retry-After'] ?? 3600) / 60));

        // Jamais le mot de passe tenté, jamais le code d'activation tenté —
        // seulement de quoi distinguer un employé qui se trompe d'une
        // attaque : quel compte est visé, depuis quelle adresse.
        Log::warning("Limite de tentatives atteinte : {$action}.", [
            'email' => $email,
            'adresse_ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Trop de tentatives. Réessayez dans {$minutes} minute".($minutes > 1 ? 's' : '').'.',
        ], 429, $headers);
    }
}
