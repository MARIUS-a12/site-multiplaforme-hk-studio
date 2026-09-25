<?php

namespace Tests\Feature\Postgres\Commande;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Models\Etablissement;
use App\Models\HistoriqueCommande;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Support\Tenancy\ContexteEtablissement;
use Illuminate\Database\QueryException;
use Tests\PostgresTestCase;

/**
 * L'écriture dans historique_commande a lieu APRÈS la création de la
 * commande, mais AVANT celle des lignes/réservations (voir CreerCommande) —
 * toutes dans la même transaction. Une ligne à quantite=0 passe le contrôle
 * de disponibilité (0 demandé est toujours satisfait) mais viole ensuite la
 * contrainte CHECK `lignes_commande_quantite_positive` au moment de
 * l'INSERT : exactement le genre d'échec TARDIF, après que la commande et
 * son historique ont déjà été insérés dans la transaction, qui permet de
 * vérifier que le ROLLBACK les emporte bien tous les deux — pas seulement
 * un échec précoce (déjà couvert par ToutOuRienTest) où l'historique n'a
 * jamais été atteint.
 */
class HistoriqueAtomiciteTest extends PostgresTestCase
{
    public function test_un_echec_tardif_apres_lecriture_de_lhistorique_efface_tout(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        try {
            app(CreerCommande::class)->executer(
                $etablissement,
                $client,
                [new LigneCommandeDemandee($produit->id, null, 0)],
                Canal::Web,
                SourceCommande::PanierWeb,
                'cle-historique-atomicite',
            );
            $this->fail('La contrainte CHECK lignes_commande_quantite_positive aurait dû rejeter quantite=0.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('lignes_commande_quantite_positive', $e->getMessage());
        }

        $this->assertSame(0, $etablissement->commandes()->count(), 'la commande insérée avant léchec doit être annulée');
        $this->assertSame(0, HistoriqueCommande::pourTousEtablissements()->count(), 'lhistorique inséré avant léchec doit être annulé');
        $this->assertSame(0, $produit->fresh()->quantite_reservee);
    }
}
