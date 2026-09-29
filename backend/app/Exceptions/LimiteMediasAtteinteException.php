<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée à l'intérieur de la transaction d'envoi d'un média (voir
 * MediaProduitController::store) quand le nombre maximal de photos par
 * produit est atteint — y compris dans le cas rare de deux envois concurrents
 * qui passeraient tous deux la vérification rapide faite avant le traitement
 * de l'image.
 */
class LimiteMediasAtteinteException extends RuntimeException {}
