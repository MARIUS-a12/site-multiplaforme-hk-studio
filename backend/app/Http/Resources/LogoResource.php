<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Les deux formats carrés du logo (voir config/medias.php:formats_logo) —
 * "petit" pour l'en-tête, "grand" pour les affichages plus larges. Chaque
 * format expose webp (prioritaire) et jpg (repli), comme MediaResource pour
 * les photos produit.
 */
class LogoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'petit' => ['webp' => $this->urlVariante('petit', 'webp'), 'jpg' => $this->urlVariante('petit', 'jpg')],
            'grand' => ['webp' => $this->urlVariante('grand', 'webp'), 'jpg' => $this->urlVariante('grand', 'jpg')],
        ];
    }
}
