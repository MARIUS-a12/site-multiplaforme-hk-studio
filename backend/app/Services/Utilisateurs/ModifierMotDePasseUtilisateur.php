<?php

namespace App\Services\Utilisateurs;

use App\Models\JournalAudit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Après changement, invalide toutes les AUTRES sessions de l'utilisateur et
 * garde la sienne — en supprimant directement les lignes de la table
 * "sessions" (pilote "database", voir config/session.php) autres que celle
 * en cours. logoutOtherDevices() de Laravel ne suffirait pas ici : il ne
 * fait tourner que le remember_token, pas les sessions actives.
 */
class ModifierMotDePasseUtilisateur
{
    public function executer(User $utilisateur, string $nouveauMotDePasse, string $idSessionActuelle, ?string $adresseIp): void
    {
        $utilisateur->update(['password' => $nouveauMotDePasse]);

        DB::table('sessions')
            ->where('user_id', $utilisateur->id)
            ->where('id', '!=', $idSessionActuelle)
            ->delete();

        JournalAudit::create([
            'utilisateur_id' => $utilisateur->id,
            'action' => 'mot_de_passe_modifie',
            'adresse_ip' => $adresseIp,
        ]);
    }
}
