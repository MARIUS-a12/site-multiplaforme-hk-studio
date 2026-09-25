<?php

namespace App\Services\Commandes;

use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Models\Commande;
use App\Services\Stock\LibererReservation;
use Illuminate\Support\Facades\DB;

/**
 * Aucun autre service de Temps B ne couvrait la transition attente_paiement
 * → annulee : celle-ci n'existait nulle part avant l'exigence d'un
 * historique pour chacune des 4 transitions. Créée pour lui donner un point
 * d'entrée explicite, symétrique à ConsommerReservation et à l'expiration.
 */
class AnnulerCommande
{
    public function __construct(
        private readonly LibererReservation $libererReservation,
    ) {}

    public function executer(Commande $commande, ?string $motif = null, ?int $utilisateurId = null): void
    {
        DB::transaction(function () use ($commande, $motif, $utilisateurId) {
            // pourTousEtablissements() : voir la même remarque dans
            // ConsommerReservation — $commande est déjà l'instance de
            // confiance de l'appelant.
            $affectee = Commande::pourTousEtablissements()
                ->whereKey($commande->id)
                ->where('statut', StatutCommande::AttentePaiement)
                ->update(['statut' => StatutCommande::Annulee]);

            if ($affectee === 0) {
                return;
            }

            $commande->historique()->make([
                'ancien_statut' => StatutCommande::AttentePaiement,
                'nouveau_statut' => StatutCommande::Annulee,
                'utilisateur_id' => $utilisateurId,
                'motif' => $motif,
            ])->pourEtablissement($commande->etablissement_id)->save();

            foreach ($commande->reservations()->where('statut', StatutReservation::Active)->get() as $reservation) {
                $this->libererReservation->executer($reservation);
            }
        });
    }
}
