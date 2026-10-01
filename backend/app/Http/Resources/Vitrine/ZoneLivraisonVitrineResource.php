<?php

namespace App\Http\Resources\Vitrine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneLivraisonVitrineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'frais' => $this->frais,
            'delai_estime' => $this->delai_estime,
        ];
    }
}
