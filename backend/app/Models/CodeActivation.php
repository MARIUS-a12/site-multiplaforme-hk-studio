<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Correctif Étape 10 — voir GenererCodeActivation (seul point qui en crée)
 * et ActiverCompte (seul point qui en consomme). "code" est haché, jamais
 * en clair — $hidden en plus de ça, pour qu'aucune sérialisation accidentelle
 * de ce modèle (aucune n'existe aujourd'hui, mais défense en profondeur) ne
 * puisse jamais l'exposer, même haché.
 */
class CodeActivation extends Model
{
    use HasFactory;

    protected $table = 'codes_activation';

    protected $hidden = ['code'];

    protected $fillable = [
        'etablissement_id',
        'utilisateur_id',
        'code',
        'expire_le',
        'utilise_le',
        'cree_par',
    ];

    protected function casts(): array
    {
        return [
            'expire_le' => 'datetime',
            'utilise_le' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }
}
