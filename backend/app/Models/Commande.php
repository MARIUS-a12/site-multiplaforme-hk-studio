<?php

namespace App\Models;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Commande extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'commandes';

    protected $attributes = [
        'statut' => 'attente_paiement',
        'sous_total' => 0,
        'remise' => 0,
        'frais_livraison' => 0,
        'total' => 0,
    ];

    protected $fillable = [
        'client_id',
        'numero',
        'statut',
        'source',
        'canal',
        'zone_livraison_id',
        'sous_total',
        'remise',
        'frais_livraison',
        'total',
        'cle_idempotence',
        'expire_le',
        'payee_le',
        'note',
        'commune',
        'quartier',
    ];

    /**
     * jeton_acces n'est délibérément pas fillable (comme etablissement_id,
     * voir AppartientAEtablissement) : un numéro de commande seul se devine
     * (CMD-000847), ce jeton est ce qui protège la page de confirmation
     * publique — il ne doit jamais pouvoir être posé par un payload entrant,
     * seulement généré ici, côté serveur.
     */
    protected static function booted(): void
    {
        static::creating(function (self $commande): void {
            if ($commande->jeton_acces === null) {
                $commande->jeton_acces = Str::random(48);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'statut' => StatutCommande::class,
            'source' => SourceCommande::class,
            'canal' => Canal::class,
            'sous_total' => 'integer',
            'remise' => 'integer',
            'frais_livraison' => 'integer',
            'total' => 'integer',
            'expire_le' => 'datetime',
            'payee_le' => 'datetime',
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function zoneLivraison(): BelongsTo
    {
        return $this->belongsTo(ZoneLivraison::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(ReservationStock::class);
    }

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueCommande::class);
    }
}
