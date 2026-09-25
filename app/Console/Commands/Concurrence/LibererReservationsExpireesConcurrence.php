<?php

namespace App\Console\Commands\Concurrence;

use App\Jobs\LibererReservationsExpirees;
use App\Services\Stock\LibererReservation;
use Illuminate\Console\Command;

/**
 * Exécute une passe du job d'expiration dans un processus séparé — voir
 * CreerCommandeConcurrence pour le contexte (Windows, pas de pcntl_fork).
 * Sert à prouver que deux exécutions concurrentes du job sur la même
 * réservation expirée ne créditent le stock qu'une seule fois : la garantie
 * vient de l'UPDATE conditionnel sur le statut dans LibererReservation, pas
 * de ce point d'entrée, qui ne fait qu'appeler le job tel quel.
 */
class LibererReservationsExpireesConcurrence extends Command
{
    protected $signature = 'concurrence:liberer-expirees';

    protected $description = "Exécute une passe de LibererReservationsExpirees dans un processus séparé.";

    public function handle(LibererReservationsExpirees $job, LibererReservation $libererReservation): int
    {
        $job->handle($libererReservation);

        $this->line(json_encode(['ok' => true]));

        return self::SUCCESS;
    }
}
