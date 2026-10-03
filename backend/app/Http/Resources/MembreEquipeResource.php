<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\EtablissementUtilisateur
 */
class MembreEquipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'utilisateur_id' => $this->utilisateur_id,
            'nom' => $this->utilisateur->name,
            'email' => $this->utilisateur->email,
            'role' => [
                'id' => $this->role->id,
                'nom' => $this->role->nom,
                'libelle' => $this->role->libelle,
            ],
            'statut' => $this->statut,
            'ajoute_le' => $this->created_at,
            'derniere_connexion' => $this->utilisateur->derniere_connexion_a,
            'mot_de_passe_defini' => $this->utilisateur->mot_de_passe_defini,
        ];
    }
}
