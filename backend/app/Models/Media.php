<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'medias';

    protected $fillable = [
        'disque',
        'chemin',
        'url',
        'type_mime',
        'taille',
        'metadonnees_json',
    ];

    protected function casts(): array
    {
        return [
            'taille' => 'integer',
            'metadonnees_json' => 'array',
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class, 'media_produit')
            ->withPivot(['ordre', 'est_principal']);
    }
}
