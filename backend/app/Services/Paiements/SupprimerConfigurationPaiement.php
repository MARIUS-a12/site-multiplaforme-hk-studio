<?php

namespace App\Services\Paiements;

use App\Models\Etablissement;
use App\Models\JournalAudit;
use App\Models\User;
use App\Support\Vitrine\CacheVitrine;

class SupprimerConfigurationPaiement
{
    public function executer(Etablissement $etablissement, User $utilisateur, ?string $adresseIp): void
    {
        $etablissement->cinetpay_site_id = null;
        $etablissement->cinetpay_cle_api = null;
        $etablissement->cinetpay_secret = null;
        $etablissement->paiement_configure_le = null;
        $etablissement->paiement_configure_par = null;
        $etablissement->save();

        JournalAudit::create([
            'utilisateur_id' => $utilisateur->id,
            'action' => 'paiement_supprime',
            'details' => ['etablissement_id' => $etablissement->id],
            'adresse_ip' => $adresseIp,
        ]);

        CacheVitrine::invalider($etablissement->id);
    }
}
