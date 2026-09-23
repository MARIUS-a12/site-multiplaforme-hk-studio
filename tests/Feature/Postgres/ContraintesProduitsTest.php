<?php

namespace Tests\Feature\Postgres;

use App\Models\Etablissement;
use App\Models\Produit;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

/**
 * Chaque CHECK posé par la migration de garde-fous sur `produits` est
 * prouvé ici en tentant réellement l'insertion invalide sur PostgreSQL.
 *
 * mode_stock et statut sont castés en enum PHP côté modèle : une valeur
 * hors domaine y lève un ValueError avant même d'atteindre la base, donc
 * ces deux CHECK ne peuvent pas être exercés via Eloquent. On y insère en
 * SQL brut (Query Builder, sans passer par le modèle) pour prouver que la
 * base refuse seule, indépendamment de toute protection applicative.
 */
class ContraintesProduitsTest extends PostgresTestCase
{
    /**
     * quantite_stock négatif viole toujours AUSSI
     * quantite_reservee_sous_stock (une réservation >= 0 est alors
     * nécessairement supérieure à un stock négatif) : il est
     * mathématiquement impossible d'isoler produits_quantite_stock_positive
     * seule ici. On accepte donc l'une ou l'autre des deux contraintes
     * légitimement violées, plutôt que de figer un nom précis fragile.
     */
    public function test_quantite_stock_negative_est_refusee(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParUneDesContraintes(
            fn () => Produit::factory()->for($etablissement)->create(['quantite_stock' => -1]),
            ['produits_quantite_stock_positive', 'produits_quantite_reservee_sous_stock'],
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_quantite_reservee_negative_est_refusee(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => Produit::factory()->for($etablissement)->create(['quantite_reservee' => -1]),
            'produits_quantite_reservee_positive',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_quantite_reservee_superieure_au_stock_est_refusee(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => Produit::factory()->for($etablissement)->create([
                'quantite_stock' => 5,
                'quantite_reservee' => 9,
            ]),
            'produits_quantite_reservee_sous_stock',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_prix_negatif_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => Produit::factory()->for($etablissement)->create(['prix' => -100]),
            'produits_prix_positif',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_prix_barre_negatif_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => Produit::factory()->for($etablissement)->create(['prix_barre' => -100]),
            'produits_prix_positif',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_mode_stock_invalide_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => DB::table('produits')->insert($this->ligneProduitValide($etablissement, [
                'mode_stock' => 'invalide',
            ])),
            'produits_mode_stock_valide',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    public function test_statut_invalide_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        $this->assertInsertionRefuseeParContrainte(
            fn () => DB::table('produits')->insert($this->ligneProduitValide($etablissement, [
                'statut' => 'invalide',
            ])),
            'produits_statut_valide',
        );

        $this->assertSame(0, Produit::pourTousEtablissements()->count());
    }

    private function ligneProduitValide(Etablissement $etablissement, array $remplacements = []): array
    {
        return array_merge([
            'etablissement_id' => $etablissement->id,
            'nom' => 'Produit test brut',
            'slug' => 'produit-test-brut-'.uniqid(),
            'prix' => 1000,
            'mode_stock' => 'compte',
            'quantite_stock' => 0,
            'quantite_reservee' => 0,
            'disponible' => true,
            'statut' => 'brouillon',
            'created_at' => now(),
            'updated_at' => now(),
        ], $remplacements);
    }

    /**
     * Tente l'insertion à l'intérieur d'une transaction imbriquée (donc
     * d'un SAVEPOINT) : PostgreSQL avorte toute la transaction courante dès
     * qu'une instruction échoue, ce qui rendrait invalide toute requête
     * ultérieure dans le test (y compris l'assertion de comptage) si on ne
     * revenait pas au SAVEPOINT précédent la tentative.
     */
    private function assertInsertionRefuseeParContrainte(callable $insertion, string $nomContrainte): void
    {
        $this->assertInsertionRefuseeParUneDesContraintes($insertion, [$nomContrainte]);
    }

    private function assertInsertionRefuseeParUneDesContraintes(callable $insertion, array $nomsContraintes): void
    {
        try {
            DB::transaction($insertion);

            $this->fail('L\'insertion aurait dû être refusée par l\'une de ces contraintes : '.implode(', ', $nomsContraintes));
        } catch (QueryException $e) {
            $this->assertSame('23514', $e->getCode());

            $trouvee = array_filter($nomsContraintes, fn (string $nom) => str_contains($e->getMessage(), $nom));
            $this->assertNotEmpty($trouvee, "Aucune des contraintes attendues n'apparaît dans : {$e->getMessage()}");
        }
    }
}
