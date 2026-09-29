<?php

namespace App\Services\Etablissements;

use App\Models\Etablissement;

/**
 * motDePasseGenere ne transite QUE par cette valeur de retour, jamais
 * persisté en clair ni renvoyé par une autre route que celle qui a appelé
 * CreerEtablissement — voir EtablissementController::store().
 */
final class ResultatCreationEtablissement
{
    public function __construct(
        public readonly Etablissement $etablissement,
        public readonly string $motDePasseGenere,
    ) {}
}
