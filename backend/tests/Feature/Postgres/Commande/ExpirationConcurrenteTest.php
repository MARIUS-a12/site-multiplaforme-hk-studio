<?php

namespace Tests\Feature\Postgres\Commande;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Enums\StatutCommande;
use App\Enums\StatutReservation;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use Tests\ConcurrenceTestCase;

/**
 * LibererReservation garantit l'idempotence par un UPDATE conditionnel sur le
 * statut (active → liberee, seulement si encore active) : ce test prouve que
 * cette garantie tient sous une VRAIE course, pas seulement au rejeu
 * séquentiel (déjà couvert en SQLite, Temps B). Deux exécutions du job
 * LibererReservationsExpirees lancées en même temps sur la même réservation
 * expirée ne doivent créditer le stock qu'une fois, et n'écrire qu'une seule
 * ligne d'historique d'expiration.
 */
class ExpirationConcurrenteTest extends ConcurrenceTestCase
{
    public function test_deux_executions_paralleles_du_job_ne_creditent_le_stock_quune_fois(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $commande = app(CreerCommande::class)->executer(
            $etablissement,
            $client,
            [new LigneCommandeDemandee($produit->id, null, 4)],
            Canal::Web,
            SourceCommande::PanierWeb,
            'cle-expiration-concurrente',
        );
        $this->assertSame(4, $produit->fresh()->quantite_reservee);

        // Le délai de paiement est dépassé (commit réel : visible des deux
        // processus enfants).
        $commande->forceFill(['expire_le' => now()->subMinute()])->save();
        $commande->reservations()->update(['expire_le' => now()->subMinute()]);

        $bruts = $this->executerEnParallele([
            $this->commandeLibererExpirees(),
            $this->commandeLibererExpirees(),
        ]);
        $resultats = array_map($this->decoderResultat(...), $bruts);

        fwrite(STDERR, "\n[expiration-concurrente] 2 exécutions du job en parallèle, sortie de chacune :\n"
            .$this->resume($resultats)."\n");

        foreach ($resultats as $resultat) {
            $this->assertTrue($resultat['ok']);
        }

        $produit->refresh();
        $this->assertSame(0, $produit->quantite_reservee, 'le stock ne doit être crédité quune seule fois');
        $this->assertSame(10, $produit->quantite_stock);

        $commande->refresh();
        $this->assertSame(StatutCommande::Expiree, $commande->statut);
        $this->assertSame(StatutReservation::Liberee, $commande->reservations()->first()->statut);

        // Historique : création, puis UNE SEULE ligne d'expiration malgré les
        // deux exécutions concurrentes du job.
        $this->assertSame(2, $commande->historique()->count(), 'le job concurrent ne doit journaliser qu\'une fois');
    }
}
