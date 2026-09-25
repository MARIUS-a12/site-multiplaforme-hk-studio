<?php

namespace App\Models\Concerns;

use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

/**
 * `@mixin` ne résout pas les appels statiques d'un trait vers les méthodes
 * de Model (limite de l'analyseur, pas du code — ces méthodes existent bien
 * une fois le trait mélangé à un vrai modèle) : les trois `@method static`
 * ci-dessous déclarent explicitement les signatures utilisées plus bas, seul
 * moyen fiable de faire taire les faux positifs sur `addGlobalScope`,
 * `creating` et `withoutGlobalScope`.
 *
 * @method static void addGlobalScope(Scope $scope)
 * @method static void creating(\Closure $callback)
 * @method static Builder withoutGlobalScope(Scope|string $scope)
 *
 * @mixin Model
 */
trait AppartientAEtablissement
{
    public static function bootAppartientAEtablissement(): void
    {
        static::addGlobalScope(new ScopeEtablissement);

        static::creating(function (Model $model) {
            if ($model->getAttribute('etablissement_id') !== null) {
                return;
            }

            $contexte = app(ContexteEtablissement::class);

            if ($contexte->estDefini()) {
                $model->setAttribute('etablissement_id', $contexte->id());
            }
        });
    }

    public static function pourTousEtablissements(): Builder
    {
        return static::withoutGlobalScope(ScopeEtablissement::class);
    }

    /**
     * Renseigne explicitement l'établissement d'un modèle pas encore
     * enregistré. Aucun modèle ne met `etablissement_id` dans son `$fillable`
     * (c'est la règle du projet, vérifiée par AppartientAEtablissementTest) :
     * l'assignation directe est donc le seul chemin, celui qu'emprunte déjà
     * l'événement "creating" ci-dessus — en passant par getAttribute/
     * setAttribute plutôt que par la propriété magique `etablissement_id`,
     * qu'aucun analyseur statique ne peut voir sur un trait. Utile à un
     * service qui connaît l'établissement d'un parent de confiance et doit
     * le recopier sur un enfant — ex. une ligne de commande, dont la relation
     * ne porte que `commande_id`.
     *
     * Réassigner un établissement déjà fixé déplacerait la ligne d'un
     * établissement à l'autre : c'est exactement la fuite que le scope global
     * existe pour empêcher, donc on échoue bruyamment plutôt que de la
     * laisser passer silencieusement.
     */
    public function pourEtablissement(int $etablissementId): static
    {
        $actuel = $this->getAttribute('etablissement_id');

        if ($actuel !== null && (int) $actuel !== $etablissementId) {
            throw new RuntimeException(
                "Ce [{$this->getTable()}] appartient déjà à l'établissement #{$actuel} : ".
                "le réassigner à l'établissement #{$etablissementId} le ferait changer d'établissement."
            );
        }

        $this->setAttribute('etablissement_id', $etablissementId);

        return $this;
    }
}
