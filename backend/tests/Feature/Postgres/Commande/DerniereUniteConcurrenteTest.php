<?php

namespace Tests\Feature\Postgres\Commande;

use App\Models\Etablissement;
use App\Models\Produit;
use Tests\ConcurrenceTestCase;

/**
 * Décision 1 (voir CreerCommande) : la réservation est un UPDATE conditionnel
 * atomique, jamais un SELECT ... FOR UPDATE. Ce test le prouve pour de vrai :
 * NB_PROCESSUS processus indépendants, chacun avec sa propre connexion PDO,
 * visent EN MÊME TEMPS la dernière unité d'un même article. Si l'atomicité
 * n'était qu'apparente (ex. lecture puis écriture en deux temps), plusieurs
 * réussiraient. Couvre les deux chemins de réservation : produit sans
 * variante (compteurs sur `produits`) et produit avec variante (compteurs
 * sur `variantes_produit`).
 */
class DerniereUniteConcurrenteTest extends ConcurrenceTestCase
{
    private const NB_PROCESSUS = 5;

    public function test_un_seul_processus_reserve_la_derniere_unite_dun_produit_sans_variante(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 1,
            'quantite_reservee' => 0,
        ]);

        $commandes = [];
        for ($i = 0; $i < self::NB_PROCESSUS; $i++) {
            $commandes[] = $this->commandeCreerCommande(
                $etablissement,
                $client,
                [['produit_id' => $produit->id, 'variante_id' => null, 'quantite' => 1]],
                "cle-derniere-unite-produit-{$i}",
            );
        }

        $bruts = $this->executerEnParallele($commandes);
        $resultats = array_map($this->decoderResultat(...), $bruts);

        fwrite(STDERR, "\n[derniere-unite / produit sans variante] "
            .self::NB_PROCESSUS." processus, sortie de chacun :\n".$this->resume($resultats)."\n");

        $reussites = array_values(array_filter($resultats, fn ($r) => $r['ok'] === true));
        $echecs = array_values(array_filter($resultats, fn ($r) => $r['ok'] === false));

        $this->assertCount(
            1,
            $reussites,
            'Exactement 1 des '.self::NB_PROCESSUS." processus doit réussir.\n".$this->resume($resultats)
        );
        $this->assertCount(self::NB_PROCESSUS - 1, $echecs);

        foreach ($echecs as $echec) {
            $this->assertSame('indisponible', $echec['raison']);
            $this->assertStringContainsString($produit->nom, $echec['article']);
        }

        $produit->refresh();
        $this->assertSame(1, $produit->quantite_reservee);
        $this->assertSame(1, $produit->quantite_stock);
    }

    public function test_un_seul_processus_reserve_la_derniere_unite_dune_variante(): void
    {
        $etablissement = $this->nettoyerAvec(Etablissement::factory()->boutique()->create());
        $client = $etablissement->clients()->create(['nom' => 'Client Concurrence']);
        $produit = Produit::factory()->for($etablissement)->avecVariantes(1)->create();
        $variante = $produit->variantes()->first();
        $variante->update(['quantite_stock' => 1, 'quantite_reservee' => 0]);

        $commandes = [];
        for ($i = 0; $i < self::NB_PROCESSUS; $i++) {
            $commandes[] = $this->commandeCreerCommande(
                $etablissement,
                $client,
                [['produit_id' => $produit->id, 'variante_id' => null, 'quantite' => 1]],
                "cle-derniere-unite-variante-{$i}",
            );
        }

        $bruts = $this->executerEnParallele($commandes);
        $resultats = array_map($this->decoderResultat(...), $bruts);

        fwrite(STDERR, "\n[derniere-unite / variante] "
            .self::NB_PROCESSUS." processus, sortie de chacun :\n".$this->resume($resultats)."\n");

        $reussites = array_values(array_filter($resultats, fn ($r) => $r['ok'] === true));
        $echecs = array_values(array_filter($resultats, fn ($r) => $r['ok'] === false));

        $this->assertCount(
            1,
            $reussites,
            'Exactement 1 des '.self::NB_PROCESSUS." processus doit réussir.\n".$this->resume($resultats)
        );
        $this->assertCount(self::NB_PROCESSUS - 1, $echecs);

        foreach ($echecs as $echec) {
            $this->assertSame('indisponible', $echec['raison']);
        }

        $variante->refresh();
        $this->assertSame(1, $variante->quantite_reservee);
        $this->assertSame(1, $variante->quantite_stock);
    }
}
