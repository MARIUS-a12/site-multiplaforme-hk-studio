<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * On ne détruit jamais un historique de ventes (voir SupprimerEtablissement) :
 * la présence d'au moins une commande bloque toute suppression définitive.
 * Transporte le compte exact pour que le message nomme ce qui bloque.
 */
final class EtablissementAvecCommandesException extends RuntimeException
{
    private function __construct(public readonly int $nombreCommandes, string $message)
    {
        parent::__construct($message);
    }

    public static function pour(int $nombreCommandes): self
    {
        $message = $nombreCommandes === 1
            ? 'Impossible de supprimer : 1 commande existe pour cet établissement.'
            : "Impossible de supprimer : {$nombreCommandes} commandes existent pour cet établissement.";

        return new self($nombreCommandes, $message);
    }
}
