<?php

namespace App\Models;

use App\Enums\StatutEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Etablissement extends Model
{
    use HasFactory;

    protected $table = 'etablissements';

    /**
     * Miroir des défauts posés en base, pour qu'un établissement tout juste
     * créé (avant tout rechargement) expose déjà des valeurs exploitables —
     * même pattern que Produit/Categorie. Sans lui, CreerEtablissement
     * renvoyait un statut null dans sa réponse de création alors que la
     * ligne en base portait bien "actif".
     */
    protected $attributes = [
        'statut' => 'actif',
        'fuseau_horaire' => 'Africa/Abidjan',
        'devise' => 'XOF',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutEtablissement::class,
        ];
    }

    protected $fillable = [
        'nom',
        'slug',
        'type',
        'raison_sociale',
        'email',
        'telephone',
        'statut',
        'fuseau_horaire',
        'devise',
    ];

    public function domaines(): HasMany
    {
        return $this->hasMany(Domaine::class);
    }

    public function parametreSite(): HasOne
    {
        return $this->hasOne(ParametreSite::class);
    }

    public function appartenances(): HasMany
    {
        return $this->hasMany(EtablissementUtilisateur::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Categorie::class);
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function zonesLivraison(): HasMany
    {
        return $this->hasMany(ZoneLivraison::class);
    }

    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function estActif(): bool
    {
        return $this->statut === StatutEtablissement::Actif;
    }

    public function estRestaurant(): bool
    {
        return $this->type === 'restaurant';
    }
}
