<?php

namespace App\Services\Etablissements;

use App\Exceptions\CouleurAccentIllisibleException;
use App\Models\Etablissement;
use App\Support\Couleur\ContrasteCouleur;
use App\Support\Telephone\NormaliseurTelephone;
use App\Support\Vitrine\CacheVitrine;

/**
 * Un seul service pour les deux chemins (commerçant, super-admin) — voir
 * IdentiteEtablissementController, qui ne diffère que sur l'autorisation.
 * $donnees ne contient que les clés réellement envoyées (voir
 * $request->validated(), qui n'en garde que les champs présents) : un champ
 * absent du corps de la requête reste donc inchangé, jamais remis à null.
 */
class MettreAJourIdentiteEtablissement
{
    public function executer(Etablissement $etablissement, array $donnees): Etablissement
    {
        if (array_key_exists('couleur_accent', $donnees) && $donnees['couleur_accent'] !== null) {
            if (! ContrasteCouleur::estSuffisantAvecBlanc($donnees['couleur_accent'])) {
                throw CouleurAccentIllisibleException::pour(
                    $donnees['couleur_accent'],
                    ContrasteCouleur::assombrirJusquau($donnees['couleur_accent']),
                );
            }
        }

        if (array_key_exists('telephone_whatsapp', $donnees) && $donnees['telephone_whatsapp'] !== null) {
            $donnees['telephone_whatsapp'] = NormaliseurTelephone::normaliser($donnees['telephone_whatsapp']);
        }

        $etablissement->update($donnees);

        CacheVitrine::invalider($etablissement->id);

        return $etablissement->fresh();
    }
}
