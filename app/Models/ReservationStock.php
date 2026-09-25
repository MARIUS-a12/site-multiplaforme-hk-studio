<?php

namespace App\Models;

use App\Enums\StatutReservation;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationStock extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'reservations_stock';

    protected $attributes = [
        'statut' => 'active',
    ];

    protected $fillable = [
        'commande_id',
        'produit_id',
        'variante_id',
        'quantite',
        'statut',
        'expire_le',
        'liberee_le',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'statut' => StatutReservation::class,
            'expire_le' => 'datetime',
            'liberee_le' => 'datetime',
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
