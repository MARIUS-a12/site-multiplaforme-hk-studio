<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Contraste insuffisant avec le texte blanc du bouton principal de la
 * vitrine (voir ContrasteCouleur) : porte la variante assombrie suggérée,
 * que le commerçant accepte d'un clic côté frontend plutôt que de deviner
 * une couleur au hasard jusqu'à ce qu'une passe.
 */
class CouleurAccentIllisibleException extends RuntimeException
{
    private function __construct(
        public readonly string $couleurRefusee,
        public readonly string $couleurSuggeree,
    ) {
        parent::__construct(
            "Cette couleur est trop claire pour rester lisible sur la vitrine. Essayez {$couleurSuggeree} à la place.",
        );
    }

    public static function pour(string $couleurRefusee, string $couleurSuggeree): self
    {
        return new self($couleurRefusee, $couleurSuggeree);
    }
}
