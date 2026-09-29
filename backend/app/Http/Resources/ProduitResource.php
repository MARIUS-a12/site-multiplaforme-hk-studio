<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    /**
     * etablissement_id n'apparaît jamais ici : c'est une colonne interne de
     * tenancy, jamais une information utile au client (voir Étape 2, règle
     * n°6).
     *
     * "medias" (triées par ordre, la première est la principale) n'apparaît
     * que si la relation a été chargée en amont (voir ProduitController::
     * index/show, qui l'eager-loadent systématiquement) — whenLoaded() évite
     * sinon une requête N+1 silencieuse.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'categorie_id' => $this->categorie_id,
            'nom' => $this->nom,
            'slug' => $this->slug,
            'description' => $this->description,
            'reference' => $this->reference,
            'prix' => $this->prix,
            'prix_barre' => $this->prix_barre,
            'mode_stock' => $this->mode_stock,
            'quantite_stock' => $this->quantite_stock,
            'quantite_reservee' => $this->quantite_reservee,
            'disponible' => $this->disponible,
            'statut' => $this->statut,
            'publie_le' => $this->publie_le,
            'medias' => MediaResource::collection($this->whenLoaded('medias')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
