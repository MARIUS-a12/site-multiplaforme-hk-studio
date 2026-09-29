<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * Chaque variante expose webp (servi en priorité) et jpg (repli pour un
     * navigateur qui ne supporte pas WebP) : au frontend de les poser dans un
     * <picture>, voir la zone d'envoi du formulaire produit.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ordre' => $this->pivot?->ordre,
            'est_principal' => (bool) ($this->pivot?->est_principal ?? false),
            'vignette' => ['webp' => $this->urlVariante('vignette', 'webp'), 'jpg' => $this->urlVariante('vignette', 'jpg')],
            'moyenne' => ['webp' => $this->urlVariante('moyenne', 'webp'), 'jpg' => $this->urlVariante('moyenne', 'jpg')],
            'grande' => ['webp' => $this->urlVariante('grande', 'webp'), 'jpg' => $this->urlVariante('grande', 'jpg')],
        ];
    }
}
