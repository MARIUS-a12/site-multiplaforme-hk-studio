<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EtablissementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => $this->type,
            'sous_domaine' => $this->domaines->firstWhere('est_principal', true)?->hote
                ?? $this->domaines->first()?->hote,
            'statut' => $this->statut,
        ];
    }
}
