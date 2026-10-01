<?php

namespace App\Http\Requests\Vitrine;

use App\Enums\StatutZoneLivraison;
use App\Rules\NumeroTelephoneIvoirien;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Le panier (lignes) n'arrive ici que par identifiants et quantités : un
 * "prix" ou un "total" envoyé par le navigateur n'est même pas dans la liste
 * des champs validés, donc $request->validated() ne les reprend jamais — le
 * serveur ne peut littéralement pas les lire, encore moins les utiliser (voir
 * VitrineCommandeController, qui recalcule tout via CreerCommande).
 */
class CreerCommandeVitrineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.produit_id' => ['required', 'integer'],
            'lignes.*.variante_id' => ['nullable', 'integer'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],

            'client' => ['required', 'array'],
            'client.nom' => ['required', 'string', 'max:255'],
            'client.telephone' => ['required', 'string', new NumeroTelephoneIvoirien],
            'client.email' => ['nullable', 'email', 'max:255'],

            // Scopé à l'établissement courant explicitement : Rule::exists
            // interroge la table directement, sans passer par Eloquent — le
            // scope global tenant-isolé (ScopeEtablissement) ne s'applique
            // donc pas ici tout seul.
            'zone_livraison_id' => [
                'nullable',
                'integer',
                Rule::exists('zones_livraison', 'id')
                    ->where('etablissement_id', app(ContexteEtablissement::class)->id())
                    ->where('statut', StatutZoneLivraison::Actif->value),
            ],

            'note' => ['nullable', 'string', 'max:500'],
            'cle_idempotence' => ['required', 'string', 'max:255'],

            // Étape 6C-1 : aucun appel CinetPay n'existe encore, mais le
            // refus, lui, doit déjà exister — ne jamais se fier à
            // l'interface (qui masque déjà le bouton) pour l'empêcher.
            'paiement_en_ligne' => [
                'sometimes',
                'boolean',
                function (string $attribute, mixed $value, callable $fail): void {
                    if ($value && ! app(ContexteEtablissement::class)->obtenir()?->paiementEstConfigure()) {
                        $fail("Le paiement en ligne n'est pas disponible pour cet établissement.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'zone_livraison_id.exists' => "Cette zone de livraison n'est plus disponible.",
        ];
    }
}
