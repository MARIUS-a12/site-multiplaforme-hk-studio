<?php

namespace Tests\Feature\Postgres\Commande;

use App\Models\Etablissement;
use App\Models\Produit;
use Tests\ConcurrenceTestCase;

/**
 * GenererNumeroCommande s'appuie sur `nextval('commandes_numero_seq')` :
 * contrairement à un MAX(numero)+1, une séquence Postgres ne verrouille
 * jamais — ce test vérifie que ce choix tient sa promesse sous une VRAIE
 * charge concurrente (N processus indépendants), pas seulement qu'il produit
 * des valeurs différentes en série.
 */
class NumerotationConcurrenteTest extends ConcurrenceTestCase
{
    private const NB_PROCESSUS = 10;

    public function test_n_commandes_creees_en_parallele_recoivent_n_numeros_distincts(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 1000,
            'quantite_reservee' => 0,
        ]);

        $commandes = [];
        for ($i = 0; $i < self::NB_PROCESSUS; $i++) {
            $commandes[] = $this->commandeCreerCommande(
                $etablissement,
                $client,
                [['produit_id' => $produit->id, 'variante_id' => null, 'quantite' => 1]],
                "cle-numerotation-{$i}",
            );
        }

        $bruts = $this->executerEnParallele($commandes);
        $resultats = array_map($this->decoderResultat(...), $bruts);

        fwrite(STDERR, "\n[numerotation-concurrente] "
            .self::NB_PROCESSUS." processus, numéros obtenus :\n".$this->resume($resultats)."\n");

        foreach ($resultats as $resultat) {
            $this->assertTrue($resultat['ok']);
            $this->assertMatchesRegularExpression('/^CMD-\d{6,}$/', $resultat['numero']);
        }

        $numeros = array_column($resultats, 'numero');
        $this->assertCount(
            self::NB_PROCESSUS,
            array_unique($numeros),
            "Des numéros en double sont apparus sous concurrence :\n".$this->resume($resultats)
        );
    }
}
