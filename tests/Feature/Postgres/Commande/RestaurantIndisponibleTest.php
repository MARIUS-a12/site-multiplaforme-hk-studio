<?php

namespace Tests\Feature\Postgres\Commande;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Exceptions\ArticleIndisponibleException;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Models\ReservationStock;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use App\Support\Tenancy\ContexteEtablissement;
use Tests\PostgresTestCase;

/**
 * Décision 2 : mode interrupteur (restaurant), on vérifie `disponible`, on
 * ne crée AUCUNE réservation. Un plat marqué indisponible est refusé, et
 * surtout : aucune ligne ne doit exister dans reservations_stock, même après
 * l'échec — ce test le vérifie sur reservations_stock directement, pas
 * seulement via le compte de la commande.
 */
class RestaurantIndisponibleTest extends PostgresTestCase
{
    public function test_un_plat_indisponible_est_refuse_sans_creer_de_reservation(): void
    {
        $etablissement = Etablissement::factory()->restaurant()->create();
        app(ContexteEtablissement::class)->definir($etablissement);
        $client = $etablissement->clients()->create(['nom' => 'Client Resto']);
        $plat = Produit::factory()->for($etablissement)->create(['disponible' => false]);

        try {
            app(CreerCommande::class)->executer(
                $etablissement,
                $client,
                [new LigneCommandeDemandee($plat->id, null, 1)],
                Canal::Whatsapp,
                SourceCommande::BoutonWhatsapp,
                'cle-restaurant-indisponible',
            );
            $this->fail('ArticleIndisponibleException attendue');
        } catch (ArticleIndisponibleException $e) {
            $this->assertStringContainsString($plat->nom, $e->nomArticle());
        }

        $this->assertSame(0, $etablissement->commandes()->count());
        $this->assertSame(0, ReservationStock::pourTousEtablissements()->count());
    }
}
