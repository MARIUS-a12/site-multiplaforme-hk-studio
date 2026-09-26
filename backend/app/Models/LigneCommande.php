<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneCommande extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'lignes_commande';

    protected $fillable = [
        'commande_id',
        'produit_id',
        'variante_id',
        'nom_produit_capture',
        'reference_capture',
        'prix_unitaire',
        'quantite',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'prix_unitaire' => 'integer',
            'quantite' => 'integer',
            'total' => 'integer',
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProduit::class);
    }
}
