<?php

namespace Tests\Feature\Catalogue;

use App\Models\Categorie;
use App\Models\Etablissement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesApiTest extends TestCase
{
    use RefreshDatabase;

    private function depuis(string $hote): static
    {
        return $this->withHeader('Referer', "http://{$hote}:8000");
    }

    private function connecte(string $hote, string $email): static
    {
        $this->depuis($hote)->postJson("http://{$hote}:8000/api/connexion", [
            'email' => $email,
            'mot_de_passe' => 'motdepasse',
        ])->assertStatus(200);

        return $this;
    }

    /**
     * "Sacs", "sacs" et "Sacs  " doivent renvoyer la même catégorie
     * existante (200), jamais en créer une deuxième (201) — la casse et les
     * espaces en trop ne comptent pas comme un nom différent.
     */
    public function test_creation_deduplique_par_nom_insensible_a_la_casse_et_aux_espaces(): void
    {
        $this->seed();
        $this->connecte('chez-awa.localhost', 'awa@chez-awa.test');

        $chezAwa = Etablissement::where('slug', 'chez-awa')->firstOrFail();
        $totalAvant = Categorie::pourTousEtablissements()->where('etablissement_id', $chezAwa->id)->count();

        $premiere = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/categories', [
            'nom' => 'Sacs',
        ]);
        $premiere->assertStatus(201);
        $id = $premiere->json('data.id');

        $doublonCasse = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/categories', [
            'nom' => 'sacs',
        ]);
        $doublonCasse->assertStatus(200);
        $doublonCasse->assertJsonPath('data.id', $id);

        $doublonEspaces = $this->depuis('chez-awa.localhost')->postJson('http://chez-awa.localhost:8000/api/categories', [
            'nom' => 'Sacs  ',
        ]);
        $doublonEspaces->assertStatus(200);
        $doublonEspaces->assertJsonPath('data.id', $id);

        $totalApres = Categorie::pourTousEtablissements()->where('etablissement_id', $chezAwa->id)->count();
        $this->assertSame($totalAvant + 1, $totalApres, 'une seule catégorie "Sacs" doit avoir été créée');
    }
}
