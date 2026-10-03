<?php

namespace Tests\Feature\Postgres;

use App\Models\Etablissement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\PostgresTestCase;

/**
 * UNIQUE(etablissement_id, email) sur "users" — voir la migration
 * "ajouter_etablissement_id_a_users_table". Insertion brute via le Query
 * Builder (pas User::create()) pour vérifier la contrainte EN BASE
 * elle-même, indépendamment de toute validation applicative qui pourrait,
 * un jour, être retirée ou contournée.
 */
class ContraintesUtilisateursTest extends PostgresTestCase
{
    public function test_meme_email_deux_fois_dans_le_meme_etablissement_est_refuse(): void
    {
        $etablissement = Etablissement::factory()->create();

        User::factory()->create(['email' => 'doublon@test.local', 'etablissement_id' => $etablissement->id]);

        try {
            DB::transaction(function () use ($etablissement) {
                DB::table('users')->insert([
                    'name' => 'Second compte',
                    'email' => 'doublon@test.local',
                    'password' => 'peu-importe',
                    'etablissement_id' => $etablissement->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            $this->fail("L'insertion aurait dû être refusée par la contrainte UNIQUE(etablissement_id, email).");
        } catch (QueryException $e) {
            $this->assertSame('23505', $e->getCode());
            $this->assertStringContainsString('users_etablissement_id_email_unique', $e->getMessage());
        }

        $this->assertSame(1, User::where('email', 'doublon@test.local')->count());
    }

    public function test_meme_email_dans_deux_etablissements_differents_est_accepte(): void
    {
        $etablissementA = Etablissement::factory()->create();
        $etablissementB = Etablissement::factory()->create();

        User::factory()->create(['email' => 'partage@test.local', 'etablissement_id' => $etablissementA->id]);

        DB::table('users')->insert([
            'name' => 'Compte du second établissement',
            'email' => 'partage@test.local',
            'password' => 'peu-importe',
            'etablissement_id' => $etablissementB->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(2, User::where('email', 'partage@test.local')->count());
    }
}
