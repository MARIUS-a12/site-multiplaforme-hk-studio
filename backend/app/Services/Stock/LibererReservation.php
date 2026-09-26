<?php

namespace App\Services\Stock;

use App\Enums\StatutReservation;
use App\Models\Produit;
use App\Models\ReservationStock;
use App\Models\VarianteProduit;
use Illuminate\Support\Facades\DB;

class LibererReservation
{
    /**
     * Fait repasser une réservation active à `liberee` et rend
     * `quantite_reservee` à la variante, dans la même transaction. Le
     * passage de statut est lui-même l'UPDATE conditionnel qui rend
     * l'opération idempotente : si la réservation n'est déjà plus `active`
     * (déjà libérée, ou consommée par un paiement entre-temps), 0 ligne est
     * affectée et rien d'autre ne se produit — rejouer ne libère jamais le
     * stock une seconde fois.
     */
    public function executer(ReservationStock $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            // pourTousEtablissements() : $reservation est déjà l'instance de
            // confiance que l'appelant a chargée (job d'expiration, service
            // de paiement...) ; on agit sur cette ligne précise par id, ce
            // n'est pas une lecture tenant-scopée qui doit dépendre d'un
            // contexte ambiant éventuellement absent (ex. worker de file).
            $affectee = ReservationStock::pourTousEtablissements()
                ->whereKey($reservation->id)
                ->where('statut', StatutReservation::Active)
                ->update(['statut' => StatutReservation::Liberee, 'liberee_le' => now()]);

            if ($affectee === 0) {
                return;
            }

            if ($reservation->variante_id !== null) {
                VarianteProduit::libererAtomiquement($reservation->variante_id, $reservation->quantite);
            } else {
                Produit::libererAtomiquement($reservation->produit_id, $reservation->quantite);
            }
        });
    }
}
