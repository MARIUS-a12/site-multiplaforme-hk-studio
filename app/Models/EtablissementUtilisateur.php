<?php

namespace App\Models;

use App\Enums\StatutMembre;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Volontairement SANS AppartientAEtablissement : SessionController
 * interroge cette table AVANT que resoudre.etablissement n'ait posé de
 * contexte (la connexion doit d'abord savoir si l'utilisateur a le droit
 * d'être là) — avec le scope global, cette requête sans contexte ambiant
 * lèverait, alors que le paramètre `$etablissement`, explicite, est déjà la
 * bonne portée à interroger.
 *
 * Le super-admin n'a ici AUCUNE ligne : voir User::est_super_admin.
 */
class EtablissementUtilisateur extends Model
{
    use HasFactory;

    protected $table = 'etablissement_utilisateurs';

    protected $attributes = [
        'statut' => 'actif',
    ];

    protected $fillable = [
        'etablissement_id',
        'utilisateur_id',
        'role_id',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutMembre::class,
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
