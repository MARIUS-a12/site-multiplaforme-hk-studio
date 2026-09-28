<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée par ScopeEtablissement quand une requête interroge un modèle
 * tenant-scopé sans qu'aucun établissement ne soit résolu dans le contexte —
 * le cas normal du super-admin, dont l'hôte dédié n'en résout jamais un, ou
 * un traitement en tâche de fond (job, seeder, commande artisan) qui a
 * oublié d'appeler explicitement Modele::pourTousEtablissements().
 *
 * Une classe dédiée plutôt qu'une RuntimeException générique : ça permet à
 * bootstrap/app.php de la traduire en 400 précisément, sans risquer
 * d'avaler par erreur une RuntimeException qui n'a rien à voir avec la
 * tenancy.
 */
class EtablissementNonResoluException extends RuntimeException {}
