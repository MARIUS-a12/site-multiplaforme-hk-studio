<?php

namespace App\Models;

use App\Enums\StatutZoneLivraison;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneLivraison extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'zones_livraison';

    protected $attributes = [
        'frais' => 0,
        'statut' => 'actif',
    ];

    protected $fillable = [
        'nom',
        'frais',
        'delai_estime',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'frais' => 'integer',
            'statut' => StatutZoneLivraison::class,
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }
}
