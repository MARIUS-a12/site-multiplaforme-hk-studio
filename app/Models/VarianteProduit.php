<?php

namespace App\Models;

use App\Enums\StatutVariante;
use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VarianteProduit extends Model
{
    use AppartientAEtablissement, HasFactory;

    protected $table = 'variantes_produit';

    /**
     * Miroir des défauts posés en base, pour qu'une variante tout juste
     * créée expose déjà des valeurs exploitables sans rechargement.
     */
    protected $attributes = [
        'statut' => 'actif',
        'quantite_stock' => 0,
        'quantite_reservee' => 0,
        'disponible' => true,
    ];

    protected $fillable = [
        'produit_id',
        'nom',
        'reference',
        'prix',
        'quantite_stock',
        'quantite_reservee',
        'disponible',
        'attributs_json',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'quantite_stock' => 'integer',
            'quantite_reservee' => 'integer',
            'disponible' => 'boolean',
            'attributs_json' => 'array',
            'statut' => StatutVariante::class,
        ];
    }

    public function etablissement(): BelongsTo
    {
        return $this->belongsTo(Etablissement::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    /**
     * Le mode de stock (compte / interrupteur) est une propriété du produit
     * parent, même quand — comme ici — le stock physique vit sur la
     * variante : cette méthode applique ce mode à ses propres colonnes.
     *
     * Coût en requêtes : `$this->produit` déclenche un lazy load (une
     * requête) si la relation n'est pas déjà chargée. Sur une liste de
     * variantes, charge en amont avec `VarianteProduit::with('produit')`.
     * En pratique, ce chemin ne devrait servir qu'à vérifier une variante
     * précise déjà en main (ex. panier) : pour un catalogue de produits,
     * utilise `Produit::estDisponibleEnQuantite()`, qui ne passe jamais par
     * ici et n'a donc pas besoin de cette relation.
     */
    public function estDisponibleEnQuantite(int $quantite = 1): bool
    {
        return $this->produit->mode_stock->estDisponibleEnQuantite(
            $this->quantite_stock,
            $this->quantite_reservee,
            $this->disponible,
            $quantite,
        );
    }

    /**
     * Le prix d'une variante hérite de celui du produit tant qu'il n'a pas
     * été explicitement fixé.
     */
    public function prixEffectif(): int
    {
        return $this->prix ?? $this->produit->prix;
    }
}
