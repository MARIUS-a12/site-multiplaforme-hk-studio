<?php

namespace Tests\Feature\Postgres;

use App\Models\Categorie;
use App\Models\Etablissement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

/**
 * Voir ContraintesProduitsTest pour pourquoi cette insertion passe par le
 * Query Builder plutôt que par le modèle : statut est casté en enum PHP
 * côté Categorie, une valeur hors domaine y échouerait avant la base.
 */
class ContraintesCategoriesTest extends PostgresTestCase
{
    public function test_statut_invalide_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        try {
            DB::transaction(function () use ($etablissement) {
                DB::table('categories')->insert([
                    'etablissement_id' => $etablissement->id,
                    'nom' => 'Catégorie test brute',
                    'slug' => 'categorie-test-brute-'.uniqid(),
                    'ordre' => 0,
                    'statut' => 'invalide',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->fail("L'insertion aurait dû être refusée par la contrainte categories_statut_valide.");
        } catch (QueryException $e) {
            $this->assertSame('23514', $e->getCode());
            $this->assertStringContainsString('categories_statut_valide', $e->getMessage());
        }

        $this->assertSame(0, Categorie::pourTousEtablissements()->count());
    }
}
