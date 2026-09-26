<?php

namespace App\Models;

use App\Enums\StatutCategorie;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'categories';

    /**
     * Miroir du défaut posé en base, pour qu'une catégorie tout juste créée
     * expose déjà une valeur exploitable sans rechargement.
     */
    protected $attributes = [
        'statut' => 'actif',
    ];

    protected $fillable = [
        'nom',
        'slug',
        'description',
        'ordre',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'statut' => StatutCategorie::class,
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }
}
