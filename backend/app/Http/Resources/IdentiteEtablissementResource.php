<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Réponse complète du formulaire d'identité (GET /api/parametres/etablissement
 * et GET /api/etablissements/{id}/identite) — toutes les colonnes, y compris
 * celles encore nulles : contrairement à la vitrine publique
 * (EtablissementVitrineResource), ce formulaire a besoin de savoir ce qui
 * manque pour l'afficher vide, pas pour l'omettre.
 */
class IdentiteEtablissementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'couleur_accent' => $this->couleur_accent,
            'logo' => $this->logoMedia ? new LogoResource($this->logoMedia) : null,
            'telephone_whatsapp' => $this->telephone_whatsapp,
            'telephone_fixe' => $this->telephone_fixe,
            'email_contact' => $this->email_contact,
            'adresse' => $this->adresse,
            'horaires' => $this->horaires,
            'lien_facebook' => $this->lien_facebook,
            'lien_instagram' => $this->lien_instagram,
            'lien_tiktok' => $this->lien_tiktok,
            'lien_site_web' => $this->lien_site_web,
        ];
    }
}
