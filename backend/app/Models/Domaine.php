<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domaine extends Model
{
    use HasFactory;

    protected $table = 'domaines';

    protected $fillable = [
        'hote',
        'type',
        'est_principal',
        'verifie_le',
        'statut',
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'verifie_le' => 'datetime',
    ];

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    /**
     * Résout l'établissement d'un hôte SANS filtrer sur son statut : c'est à
     * l'appelant de décider quoi faire d'un établissement inactif.
     * ResoudreEtablissement refuse la requête (404) ; SessionController le
     * traite comme un échec de connexion identique aux trois autres, pour ne
     * jamais distinguer "établissement inactif" de "mauvais mot de passe"
     * dans sa réponse.
     */
    public static function pourHote(string $hote): ?Etablissement
    {
        return static::with('etablissement')->where('hote', $hote)->first()?->etablissement;
    }
}
