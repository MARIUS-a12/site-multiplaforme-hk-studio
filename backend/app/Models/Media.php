<?php

namespace App\Models;

use App\Models\Concerns\AppartientAEtablissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

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

    /**
     * URL publique d'une variante ("vignette", "moyenne" ou "grande"), au
     * format demandé ("webp" ou "jpg" — le repli). Retourne null si cette
     * variante n'a pas été générée (ne devrait pas arriver en pratique,
     * generer() les produit toutes les trois systématiquement).
     */
    public function urlVariante(string $nom, string $format = 'webp'): ?string
    {
        $chemin = $this->metadonnees_json['variantes'][$nom][$format] ?? null;

        return $chemin === null ? null : Storage::disk($this->disque)->url($chemin);
    }

    /**
     * Efface du disque les six fichiers (3 formats x 2 encodages) de ce
     * média. À appeler avant de supprimer la ligne — voir
     * MediaProduitController::destroy, seul appelant : un média encore
     * attaché à un autre produit ne doit jamais perdre ses fichiers.
     */
    public function supprimerFichiersDisque(): void
    {
        $chemins = [];

        foreach ($this->metadonnees_json['variantes'] ?? [] as $variante) {
            $chemins[] = $variante['webp'] ?? null;
            $chemins[] = $variante['jpg'] ?? null;
        }

        Storage::disk($this->disque)->delete(array_filter($chemins));
    }
}
