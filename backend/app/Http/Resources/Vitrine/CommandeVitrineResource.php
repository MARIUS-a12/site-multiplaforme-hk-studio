<?php

namespace App\Http\Resources\Vitrine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Page de confirmation publique (voir GET /api/vitrine/commandes/{numero}) :
 * suppose "lignes" et "zoneLivraison" déjà chargées par le contrôleur.
 * Jamais le jeton d'accès lui-même dans cette réponse — il est déjà dans
 * l'URL que le client a en main, pas besoin de le lui renvoyer.
 */
class CommandeVitrineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'numero' => $this->numero,
            'statut' => $this->statut->value,
            'sous_total' => $this->sous_total,
            'frais_livraison' => $this->frais_livraison,
            'total' => $this->total,
            'note' => $this->note,
            'cree_le' => $this->created_at,
            'zone_livraison' => $this->zoneLivraison ? ['nom' => $this->zoneLivraison->nom] : null,
            'lignes' => $this->lignes->map(fn ($ligne) => [
                'nom' => $ligne->nom_produit_capture,
                'quantite' => $ligne->quantite,
                'prix_unitaire' => $ligne->prix_unitaire,
                'total' => $ligne->total,
            ]),
        ];
    }
}
