<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domaine extends Model
{
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
}
