<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etablissement extends Model
{
    protected $table = 'etablissements';

    protected $fillable = [
        'nom',
        'slug',
        'type',
        'raison_sociale',
        'email',
        'telephone',
        'statut',
        'fuseau_horaire',
        'devise',
    ];

    public function domaines(): HasMany
    {
        return $this->hasMany(Domaine::class);
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function estRestaurant(): bool
    {
        return $this->type === 'restaurant';
    }
}
