<?php

namespace App\Services\Equipe;

use App\Models\EtablissementUtilisateur;

/**
 * Le code d'activation ne transite que par cet objet, jamais stocké en
 * clair nulle part ailleurs (voir GenererCodeActivation, qui ne le
 * journalise jamais non plus).
 */
final class ResultatCreationMembreEquipe
{
    public function __construct(
        public readonly EtablissementUtilisateur $membre,
        public readonly string $codeActivation,
    ) {}
}
