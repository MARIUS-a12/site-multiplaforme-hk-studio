<?php

namespace App\Services\Paiements;

use App\Models\Etablissement;
use App\Models\JournalAudit;
use App\Models\User;
use App\Support\Vitrine\CacheVitrine;

/**
 * Les trois identifiants CinetPay posés ENSEMBLE (jamais de configuration
 * partielle, voir ConfigurerPaiementEtablissementRequest) — par affectation
 * directe, pas un update() de masse : ces colonnes ne sont volontairement
 * pas fillable (voir Etablissement). Invalide le cache vitrine : le
 * bouton "Payer maintenant" doit apparaître sans attendre son TTL.
 */
class ConfigurerPaiementEtablissement
{
    public function executer(
        Etablissement $etablissement,
        string $siteId,
        string $cleApi,
        string $secret,
        User $utilisateur,
        ?string $adresseIp,
    ): Etablissement {
        $etablissement->cinetpay_site_id = $siteId;
        $etablissement->cinetpay_cle_api = $cleApi;
        $etablissement->cinetpay_secret = $secret;
        $etablissement->paiement_configure_le = now();
        $etablissement->paiement_configure_par = $utilisateur->id;
        $etablissement->save();

        JournalAudit::create([
            'utilisateur_id' => $utilisateur->id,
            'action' => 'paiement_configure',
            'details' => ['etablissement_id' => $etablissement->id],
            'adresse_ip' => $adresseIp,
        ]);

        CacheVitrine::invalider($etablissement->id);

        return $etablissement->fresh();
    }
}
