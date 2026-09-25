<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * La ligne de commande ne permet pas de déterminer sans ambiguïté quelle
 * variante réserver : aucune fourni alors que le produit en a plusieurs, ou
 * une variante fournie qui n'appartient pas au produit indiqué. Distinct
 * d'ArticleIndisponibleException, qui concerne la disponibilité et non la
 * validité de la sélection.
 */
class SelectionVarianteInvalideException extends InvalidArgumentException {}
