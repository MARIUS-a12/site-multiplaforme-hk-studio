<?php

namespace App\Validation;

use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Throwable;

/**
 * Remplace Illuminate\Validation\NotPwnedVerifier (api.pwnedpasswords.com,
 * k-anonymat) : même principe, mais avec un délai d'attente COURT et
 * explicite — jamais les 30 secondes par défaut de Laravel — et une trace
 * exploitable de chaque indisponibilité, au lieu du report() générique
 * d'origine qui finit noyé dans les logs d'erreurs.
 *
 * Ne bloque JAMAIS la création d'un mot de passe parce qu'un service tiers
 * est en panne, lent, ou renvoie une erreur : exception, délai dépassé ou
 * réponse non réussie font tous PASSER le mot de passe (verify() => true).
 * Les règles locales (longueur minimale, etc., voir ModifierMotDePasseRequest)
 * continuent seules de s'appliquer dans ce cas.
 */
class PwnedPasswordVerifier implements UncompromisedVerifier
{
    public function __construct(
        private readonly Factory $http,
        private readonly int $delaiSecondes = 2,
    ) {}

    public function verify($data): bool
    {
        $valeur = (string) ($data['value'] ?? '');
        $seuil = $data['threshold'] ?? 0;

        if ($valeur === '') {
            return false;
        }

        $hash = strtoupper(sha1($valeur));
        $prefixe = substr($hash, 0, 5);

        $lignes = $this->rechercher($prefixe);

        if ($lignes === null) {
            return true;
        }

        return ! $lignes->contains(function (string $ligne) use ($hash, $prefixe, $seuil) {
            [$suffixe, $occurrences] = explode(':', $ligne);

            return $prefixe.$suffixe === $hash && (int) $occurrences > $seuil;
        });
    }

    /**
     * null = service indisponible, pour quelque raison que ce soit (jamais
     * distingué plus finement : le traitement — laisser passer, journaliser
     * — est le même dans tous les cas). Collection (vide ou pas) sinon.
     */
    private function rechercher(string $prefixe): ?Collection
    {
        try {
            $reponse = $this->http
                ->withHeaders(['Add-Padding' => 'true'])
                ->timeout($this->delaiSecondes)
                ->get('https://api.pwnedpasswords.com/range/'.$prefixe);
        } catch (Throwable $e) {
            $this->journaliserIndisponibilite($e->getMessage());

            return null;
        }

        if (! $reponse->successful()) {
            $this->journaliserIndisponibilite('HTTP '.$reponse->status());

            return null;
        }

        return (new Stringable($reponse->body()))
            ->trim()
            ->explode("\n")
            ->filter(fn ($ligne) => str_contains($ligne, ':'));
    }

    private function journaliserIndisponibilite(string $raison): void
    {
        Log::warning('Vérification pwnedpasswords.com indisponible : mot de passe accepté sans vérification de fuite.', [
            'raison' => $raison,
        ]);
    }
}
