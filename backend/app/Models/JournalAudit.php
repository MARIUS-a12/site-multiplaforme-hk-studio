<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Une ligne par action sensible journalisée (voir migration
 * create_journaux_audit_table). Jamais de valeur sensible dans "details" —
 * seulement de quoi identifier le QUOI (nom, sous-domaine...), jamais un
 * mot de passe ni un jeton.
 */
class JournalAudit extends Model
{
    protected $table = 'journaux_audit';

    public $timestamps = false;

    protected $fillable = [
        'utilisateur_id',
        'action',
        'details',
        'adresse_ip',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'cree_le' => 'datetime',
        ];
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
