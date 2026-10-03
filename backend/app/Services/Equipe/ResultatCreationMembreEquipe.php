<?php

namespace App\Services\Equipe;

use App\Models\EtablissementUtilisateur;

/**
 * Même idée que ResultatCreationEtablissement : le mot de passe généré ne
 * transite que par cet objet, jamais stocké en clair nulle part ailleurs
 * (voir JournalAudit, qui ne le journalise jamais).
 */
final class ResultatCreationMembreEquipe
{
    public function __construct(
        public readonly EtablissementUtilisateur $membre,
        public readonly string $motDePasseGenere,
    ) {}
}
