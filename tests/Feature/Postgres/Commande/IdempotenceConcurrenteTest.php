<?php

namespace Tests\Feature\Postgres\Commande;

use App\Models\Etablissement;
use App\Models\Produit;
use Tests\ConcurrenceTestCase;

/**
 * Décision 3 (voir CreerCommande) : une clé d'idempotence déjà vue renvoie
 * la commande existante sans rien réserver. Le vrai risque n'est pas le
 * rejeu séquentiel (déjà couvert en SQLite, Temps B) mais la course : deux
 * appels avec la MÊME clé, strictement simultanés, peuvent tous deux
 * dépasser la vérification initiale avant qu'aucun n'ait rien inséré — c'est
 * exactement le chemin `catch (UniqueConstraintViolationException)` de
 * CreerCommande que ce test exerce pour de vrai.
 */
class IdempotenceConcurrenteTest extends ConcurrenceTestCase
{
    private const NB_PROCESSUS = 5;

    public function test_meme_cle_en_parallele_ne_cree_quune_commande_et_ne_reserve_quune_fois(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 100,
            'quantite_reservee' => 0,
        ]);

        $meme_cle = 'cle-idempotence-concurrente';
        $commandes = array_fill(
            0,
            self::NB_PROCESSUS,
            $this->commandeCreerCommande(
                $etablissement,
                $client,
                [['produit_id' => $produit->id, 'variante_id' => null, 'quantite' => 3]],
                $meme_cle,
            ),
        );

        $bruts = $this->executerEnParallele($commandes);
        $resultats = array_map($this->decoderResultat(...), $bruts);

        fwrite(STDERR, "\n[idempotence-concurrente] "
            .self::NB_PROCESSUS." processus, même clé, sortie de chacun :\n".$this->resume($resultats)."\n");

        foreach ($resultats as $resultat) {
            $this->assertTrue($resultat['ok'], "Un appel idempotent ne doit jamais échouer :\n".$this->resume($resultats));
        }

        $idsCommandes = array_unique(array_column($resultats, 'commande_id'));
        $this->assertCount(1, $idsCommandes, "Tous les processus doivent renvoyer LA MÊME commande :\n".$this->resume($resultats));

        $this->assertSame(1, $etablissement->commandes()->count());
        $produit->refresh();
        $this->assertSame(3, $produit->quantite_reservee, 'la quantité ne doit être réservée quune seule fois, pas '.self::NB_PROCESSUS.' fois');
    }
}
