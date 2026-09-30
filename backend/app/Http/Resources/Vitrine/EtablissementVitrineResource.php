<?php

namespace App\Http\Resources\Vitrine;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * En-tête de la vitrine (voir GET /api/vitrine/etablissement). "logo" vaut
 * toujours null pour l'instant : aucun mécanisme d'envoi de logo n'existe
 * encore côté back-office — ce champ existe déjà côté contrat pour ne pas
 * casser le frontend le jour où il sera posé.
 *
 * "numero_whatsapp" réutilise Etablissement::telephone : pas de colonne
 * dédiée, ce numéro EST celui du WhatsApp Business du commerçant en
 * pratique, et l'étape ne demande pas de distinguer les deux.
 */
class EtablissementVitrineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nom' => $this->nom,
            'type' => $this->type,
            'logo' => null,
            'numero_whatsapp' => $this->telephone,
        ];
    }
}
