<?php

namespace App\Models;

use App\Enums\ModeStock;
use App\Enums\StatutProduit;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Produit extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'produits';

    /**
     * Miroir des défauts posés en base pour les colonnes non liées à une
     * règle métier dynamique, afin qu'un modèle tout juste créé (avant tout
     * rechargement) expose déjà des valeurs exploitables. mode_stock est
     * volontairement absent : sa valeur par défaut dépend du type de
     * l'établissement et est déterminée dans booted().
     */
    protected $attributes = [
        'statut' => 'brouillon',
        'quantite_stock' => 0,
        'quantite_reservee' => 0,
        'disponible' => true,
    ];

    protected $fillable = [
        'categorie_id',
        'nom',
        'slug',
        'description',
        'reference',
        'prix',
        'prix_barre',
        'mode_stock',
        'quantite_stock',
        'quantite_reservee',
        'disponible',
        'statut',
        'publie_le',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'prix_barre' => 'integer',
            'quantite_stock' => 'integer',
            'quantite_reservee' => 'integer',
            'disponible' => 'boolean',
            'mode_stock' => ModeStock::class,
            'statut' => StatutProduit::class,
            'publie_le' => 'datetime',
        ];
    }

    /**
     * À la création, si l'appelant n'a pas fixé mode_stock explicitement,
     * il est déduit du type de l'établissement courant : compte pour une
     * boutique, interrupteur pour un restaurant.
     *
     * Cas limite — création hors contexte (commande artisan, super-admin) :
     * si etablissement_id n'a pas pu être résolu à ce stade (ni par le
     * contexte de requête, ni par une relation du type
     * `$etablissement->produits()->create()`), le type de l'établissement
     * est indéterminable. Deviner (ex. retomber sur `compte`) serait
     * silencieusement faux pour un restaurant. On échoue donc bruyamment :
     * l'appelant doit soit passer par une relation d'établissement, soit
     * fixer mode_stock explicitement.
     */
    protected static function booted(): void
    {
        static::creating(function (self $produit): void {
            if ($produit->mode_stock !== null) {
                return;
            }

            $etablissement = $produit->etablissement;

            if ($etablissement === null) {
                throw new RuntimeException(
                    "Impossible de déduire mode_stock : aucun établissement n'est associé à ce produit. ".
                    'Créez-le via $etablissement->produits()->create(...), ou renseignez mode_stock explicitement.'
                );
            }

            $produit->mode_stock = $etablissement->estRestaurant()
                ? ModeStock::Interrupteur
                : ModeStock::Compte;
        });
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function variantes(): HasMany
    {
        return $this->hasMany(VarianteProduit::class);
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_produit')
            ->withPivot(['ordre', 'est_principal']);
    }

    public function aDesVariantes(): bool
    {
        return $this->variantes->isNotEmpty();
    }

    /**
     * Seul point de lecture de la disponibilité : applique le mode de stock
     * (compte / interrupteur) et la règle produit/variante. Si le produit a
     * au moins une variante, ses propres colonnes de stock ne sont jamais
     * consultées — seule compte la disponibilité de ses variantes.
     *
     * Coût en requêtes : `$this->variantes` déclenche un lazy load (une
     * requête) si la relation n'est pas déjà chargée. Sur une liste, charge
     * TOUJOURS en amont avec `Produit::with('variantes')->get()` — cette
     * méthode ne matérialise alors rien de plus, et n'ajoute aucune requête
     * par produit itéré.
     *
     * Pour chaque variante, le mode utilisé est `$this->mode_stock` (une
     * colonne du produit courant, déjà en mémoire) et non
     * `VarianteProduit::estDisponibleEnQuantite()`, qui lirait
     * `$variante->produit` : ça rechargerait CE MÊME produit à chaque
     * variante (N+1 à l'intérieur même d'un seul produit). N'appelle donc
     * jamais cette dernière depuis ici. Ne charge pas `variantes.produit` :
     * cette méthode ne s'en sert jamais.
     */
    public function estDisponibleEnQuantite(int $quantite = 1): bool
    {
        $variantes = $this->variantes;

        if ($variantes->isNotEmpty()) {
            return $variantes->contains(
                fn (VarianteProduit $variante) => $this->mode_stock->estDisponibleEnQuantite(
                    $variante->quantite_stock,
                    $variante->quantite_reservee,
                    $variante->disponible,
                    $quantite,
                )
            );
        }

        return $this->mode_stock->estDisponibleEnQuantite(
            $this->quantite_stock,
            $this->quantite_reservee,
            $this->disponible,
            $quantite,
        );
    }

    public function scopePublies(Builder $query): Builder
    {
        return $query->where('statut', StatutProduit::Publie);
    }

    /**
     * Un produit sans variante porte son propre stock (voir
     * estDisponibleEnQuantite ci-dessus) : ces trois méthodes sont le
     * pendant, sur `produits`, de VarianteProduit::reserverAtomiquement() et
     * consorts, pour les lignes de commande qui ciblent le produit lui-même.
     *
     * pourTousEtablissements() : l'id vient toujours d'une ligne déjà résolue
     * et validée par l'appelant (CreerCommande, LibererReservation,
     * ConsommerReservation) — ce n'est pas une lecture tenant-scopée qui
     * doive dépendre d'un contexte ambiant, potentiellement absent dans un
     * job ou un webhook de paiement.
     */
    public static function reserverAtomiquement(int $produitId, int $quantite): bool
    {
        return static::pourTousEtablissements()
            ->whereKey($produitId)
            ->whereRaw('quantite_stock - quantite_reservee >= ?', [$quantite])
            ->increment('quantite_reservee', $quantite) > 0;
    }

    public static function libererAtomiquement(int $produitId, int $quantite): void
    {
        static::pourTousEtablissements()->whereKey($produitId)->decrement('quantite_reservee', $quantite);
    }

    public static function consommerAtomiquement(int $produitId, int $quantite): void
    {
        static::pourTousEtablissements()->whereKey($produitId)->update([
            'quantite_stock' => DB::raw("quantite_stock - {$quantite}"),
            'quantite_reservee' => DB::raw("quantite_reservee - {$quantite}"),
        ]);
    }
}
