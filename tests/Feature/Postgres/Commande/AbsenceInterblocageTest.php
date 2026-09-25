<?php

namespace Tests\Feature\Postgres\Commande;

use App\Models\Etablissement;
use App\Models\Produit;
use Tests\ConcurrenceTestCase;

/**
 * Décision 5 (voir CreerCommande::resoudreEtTrierLignes) : les lignes sont
 * toujours verrouillées dans un ordre déterministe (id croissant), quel que
 * soit l'ordre dans lequel l'appelant les a soumises. C'est ce qui empêche
 * deux commandes sur les deux mêmes produits, soumises en ordre inverse, de
 * s'interbloquer : les deux processus finissent par verrouiller P1 avant P2,
 * jamais l'inverse.
 *
 * Si ce tri n'existait pas, PostgreSQL détecterait lui-même l'interblocage
 * après ~1s (deadlock_timeout) et ferait échouer l'une des deux transactions
 * avec une erreur 40P01 — non capturée par CreerCommande, donc remontée
 * telle quelle par la commande concurrence:creer-commande, donc un JSON
 * invalide, donc decoderResultat() ferait échouer le test. Ce test vérifie
 * l'absence totale de cette erreur, pas seulement que les processus finissent
 * par répondre.
 */
class AbsenceInterblocageTest extends ConcurrenceTestCase
{
    private const NB_REPETITIONS = 3;

    public function test_deux_commandes_sur_les_memes_produits_en_ordre_inverse_ne_sinterbloquent_jamais(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit1 = Produit::factory()->for($etablissement)->create(['quantite_stock' => 50, 'quantite_reservee' => 0]);
        $produit2 = Produit::factory()->for($etablissement)->create(['quantite_stock' => 50, 'quantite_reservee' => 0]);

        for ($i = 0; $i < self::NB_REPETITIONS; $i++) {
            $commandeA = $this->commandeCreerCommande(
                $etablissement,
                $client,
                [
                    ['produit_id' => $produit1->id, 'variante_id' => null, 'quantite' => 1],
                    ['produit_id' => $produit2->id, 'variante_id' => null, 'quantite' => 1],
                ],
                "cle-interblocage-a-{$i}",
            );
            $commandeB = $this->commandeCreerCommande(
                $etablissement,
                $client,
                [
                    ['produit_id' => $produit2->id, 'variante_id' => null, 'quantite' => 1],
                    ['produit_id' => $produit1->id, 'variante_id' => null, 'quantite' => 1],
                ],
                "cle-interblocage-b-{$i}",
            );

            $bruts = $this->executerEnParallele([$commandeA, $commandeB], timeout: 15);
            $resultats = array_map($this->decoderResultat(...), $bruts);

            fwrite(STDERR, "\n[absence-interblocage] répétition {$i}, sortie de chaque processus :\n"
                .$this->resume($resultats)."\n");

            // La preuve n'est pas un chrono (le coût de démarrage de deux
            // processus PHP domine largement un éventuel deadlock_timeout
            // Postgres, ~1s, et rendrait ce chrono inutilisable comme signal) :
            // c'est qu'aucun des deux ne remonte l'erreur 40P01 qu'un
            // interblocage réel produirait. decoderResultat() a déjà fait
            // échouer le test si l'un des deux processus a crashé au lieu de
            // renvoyer du JSON — ces assertions vérifient l'issue attendue.
            foreach ($resultats as $resultat) {
                $this->assertTrue(
                    $resultat['ok'],
                    "Un des deux processus a échoué au lieu de simplement réussir :\n".$this->resume($resultats)
                );
            }
        }

        $produit1->refresh();
        $produit2->refresh();
        $this->assertSame(self::NB_REPETITIONS * 2, $produit1->quantite_reservee);
        $this->assertSame(self::NB_REPETITIONS * 2, $produit2->quantite_reservee);
    }
}
