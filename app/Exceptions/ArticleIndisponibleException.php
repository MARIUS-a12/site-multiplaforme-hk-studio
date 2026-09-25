<?php

namespace App\Exceptions;

use App\Models\Produit;
use App\Models\VarianteProduit;
use RuntimeException;

/**
 * Fait échouer une ligne de CreerCommande en nommant l'article fautif, pour
 * que l'appelant (ex. l'IA WhatsApp) puisse le communiquer tel quel au
 * client, sans avoir à reparser un message d'erreur générique.
 */
class ArticleIndisponibleException extends RuntimeException
{
    private function __construct(
        public readonly Produit $produit,
        public readonly ?VarianteProduit $variante,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function stockInsuffisant(Produit $produit, ?VarianteProduit $variante, int $quantiteDemandee): self
    {
        return new self(
            $produit,
            $variante,
            sprintf('Stock insuffisant pour "%s" (%d demandé(s)).', self::nomAffiche($produit, $variante), $quantiteDemandee),
        );
    }

    public static function nonDisponible(Produit $produit, ?VarianteProduit $variante = null): self
    {
        return new self(
            $produit,
            $variante,
            sprintf('"%s" n\'est pas disponible actuellement.', self::nomAffiche($produit, $variante)),
        );
    }

    public function nomArticle(): string
    {
        return self::nomAffiche($this->produit, $this->variante);
    }

    private static function nomAffiche(Produit $produit, ?VarianteProduit $variante): string
    {
        return $variante !== null ? "{$produit->nom} — {$variante->nom}" : $produit->nom;
    }
}
