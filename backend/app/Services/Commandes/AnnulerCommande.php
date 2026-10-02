<?php

namespace App\Services\Commandes;

use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Models\Commande;
use App\Models\ReservationStock;
use App\Services\Stock\LibererReservation;
use App\Services\Stock\RestaurerReservation;
use Illuminate\Support\Facades\DB;

/**
 * Deux chemins, selon l'état de la commande au moment de l'annulation —
 * c'est le stock qui en décide, pas une liste arbitraire de statuts :
 * - jamais confirmée (attente_paiement) : sa réservation est encore active,
 *   le stock physique n'a jamais bougé — on la LIBÈRE (comportement
 *   d'origine, Étape 6B, strictement inchangé ci-dessous).
 * - déjà confirmée (payee ou prete, Étape 9) : sa réservation a déjà été
 *   CONSOMMÉE par ConsommerReservation, le stock physique a déjà été
 *   décrémenté — l'annuler doit le RESTAURER (voir RestaurerReservation),
 *   une opération qui n'existait nulle part avant cette étape.
 * Livrée et déjà annulée : aucun des deux WHERE ne correspond, rien ne se
 * passe (idempotent, comme le reste du cycle de vie des commandes).
 */
class AnnulerCommande
{
    private const STATUTS_CONFIRMES = [StatutCommande::Payee, StatutCommande::Prete];

    public function __construct(
        private readonly LibererReservation $libererReservation,
        private readonly RestaurerReservation $restaurerReservation,
    ) {}

    public function executer(Commande $commande, ?string $motif = null, ?int $utilisateurId = null): void
    {
        DB::transaction(function () use ($commande, $motif, $utilisateurId) {
            $this->annulerEnAttente($commande, $motif, $utilisateurId);

            foreach (self::STATUTS_CONFIRMES as $statutActuel) {
                $this->annulerConfirmee($commande, $statutActuel, $motif, $utilisateurId);
            }
        });
    }

    /**
     * attente_paiement → annulee. Code strictement inchangé depuis l'Étape
     * 6B (voir AnnulerCommandeTest::test_annuler_libere_le_stock...).
     */
    private function annulerEnAttente(Commande $commande, ?string $motif, ?int $utilisateurId): void
    {
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

        // pourTousEtablissements() explicite, pas $commande->reservations()
        // (relation) : voir la même remarque dans ConsommerReservation.
        $reservationsActives = ReservationStock::pourTousEtablissements()
            ->where('commande_id', $commande->id)
            ->where('statut', StatutReservation::Active)
            ->get();

        foreach ($reservationsActives as $reservation) {
            $this->libererReservation->executer($reservation);
        }
    }

    /**
     * payee|prete → annulee (Étape 9). $statutActuel porte le WHERE : au
     * plus une des deux itérations de la boucle appelante affecte une ligne,
     * une commande n'ayant qu'un seul statut à la fois.
     */
    private function annulerConfirmee(Commande $commande, StatutCommande $statutActuel, ?string $motif, ?int $utilisateurId): void
    {
        $affectee = Commande::pourTousEtablissements()
            ->whereKey($commande->id)
            ->where('statut', $statutActuel)
            ->update(['statut' => StatutCommande::Annulee]);

        if ($affectee === 0) {
            return;
        }

        $commande->historique()->make([
            'ancien_statut' => $statutActuel,
            'nouveau_statut' => StatutCommande::Annulee,
            'utilisateur_id' => $utilisateurId,
            'motif' => $motif,
        ])->pourEtablissement($commande->etablissement_id)->save();

        $reservationsConsommees = ReservationStock::pourTousEtablissements()
            ->where('commande_id', $commande->id)
            ->where('statut', StatutReservation::Consommee)
            ->get();

        foreach ($reservationsConsommees as $reservation) {
            $this->restaurerReservation->executer($reservation);
        }
    }
}
