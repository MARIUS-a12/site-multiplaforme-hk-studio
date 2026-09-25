<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'nom',
        'telephone',
        'email',
        'telephone_whatsapp',
        'metadonnees_json',
    ];

    protected function casts(): array
    {
        return [
            'metadonnees_json' => 'array',
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
