<?php

namespace Tests\Feature\Postgres\Commande;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Exceptions\ArticleIndisponibleException;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Support\Tenancy\ContexteEtablissement;
use Tests\PostgresTestCase;

/**
 * Décision 4 : une commande à plusieurs lignes est tout ou rien, dans une
 * transaction. Rejoue le scénario déjà couvert en SQLite (Temps B) mais sur
 * un vrai moteur PostgreSQL, avec ses propres contraintes CHECK — pas
 * seulement pour la forme : c'est ROLLBACK, pas le code applicatif, qui doit
 * annuler les deux réservations déjà posées par les lignes 1 et 3 quand la
 * ligne 2 échoue.
 */
class ToutOuRienTest extends PostgresTestCase
{
    public function test_une_ligne_indisponible_annule_toute_la_commande_et_nomme_larticle(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);

        $produit1 = Produit::factory()->for($etablissement)->create(['quantite_stock' => 10, 'quantite_reservee' => 0]);
        $produitIndisponible = Produit::factory()->for($etablissement)->create(['quantite_stock' => 1, 'quantite_reservee' => 0]);
        $produit3 = Produit::factory()->for($etablissement)->create(['quantite_stock' => 10, 'quantite_reservee' => 0]);

        try {
            app(CreerCommande::class)->executer(
                $etablissement,
                $client,
                [
                    new LigneCommandeDemandee($produit1->id, null, 2),
                    new LigneCommandeDemandee($produitIndisponible->id, null, 5),
                    new LigneCommandeDemandee($produit3->id, null, 2),
                ],
                Canal::Web,
                SourceCommande::PanierWeb,
                'cle-tout-ou-rien',
            );
            $this->fail('ArticleIndisponibleException attendue');
        } catch (ArticleIndisponibleException $e) {
            $this->assertStringContainsString($produitIndisponible->nom, $e->nomArticle());
        }

        $this->assertSame(0, $etablissement->commandes()->count());
        $this->assertSame(0, $produit1->fresh()->quantite_reservee, 'la ligne 1, traitée avant léchec, doit être annulée');
        $this->assertSame(0, $produitIndisponible->fresh()->quantite_reservee);
        $this->assertSame(0, $produit3->fresh()->quantite_reservee);
    }
}
