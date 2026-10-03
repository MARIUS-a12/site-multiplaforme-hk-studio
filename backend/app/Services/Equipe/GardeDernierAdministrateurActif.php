<?php

namespace App\Services\Equipe;

use App\Enums\StatutMembre;
use App\Models\EtablissementUtilisateur;

/**
 * Garde-fou partagé par ModifierMembreEquipe (changement de rôle qui
 * éloignerait un membre d'admin_etablissement) et ChangerStatutMembreEquipe
 * (désactivation) : "un établissement doit toujours conserver au moins un
 * administrateur actif". Ne décide PAS elle-même quand s'appliquer — chaque
 * appelant sait quelle action il tente et donc quand l'interroger.
 */
trait GardeDernierAdministrateurActif
{
    private function existeAutreAdministrateurActif(EtablissementUtilisateur $cible): bool
    {
        return EtablissementUtilisateur::query()
            ->where('etablissement_id', $cible->etablissement_id)
            ->where('id', '!=', $cible->id)
            ->where('statut', StatutMembre::Actif)
            ->whereHas('role', fn ($requete) => $requete->where('nom', 'admin_etablissement'))
            ->exists();
    }
}
