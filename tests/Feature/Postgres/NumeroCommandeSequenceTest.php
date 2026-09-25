<?php

namespace Tests\Feature\Postgres;

use App\Enums\Canal;
use App\Enums\SourceCommande;
use App\Models\Etablissement;
use App\Models\Produit;
use App\Services\Commandes\CreerCommande;
use App\Services\Commandes\GenererNumeroCommande;
use App\Services\Commandes\LigneCommandeDemandee;
use Tests\PostgresTestCase;

/**
 * La séquence `commandes_numero_seq` est un objet PostgreSQL natif : la suite
 * rapide SQLite passe par un repli et ne peut donc rien garantir ici. Seule
 * cette suite vérifie le vrai mécanisme de numérotation.
 */
class NumeroCommandeSequenceTest extends PostgresTestCase
{
    public function test_la_sequence_produit_des_numeros_formates_et_croissants(): void
    {
        $generateur = app(GenererNumeroCommande::class);

        $premier = $generateur->executer();
        $second = $generateur->executer();

        // `%06d` garantit au moins 6 chiffres : la séquence n'est pas remise
        // à zéro par migrate:fresh (voir la migration), elle peut donc
        // dépasser 999999 au fil des exécutions de la suite.
        $this->assertMatchesRegularExpression('/^CMD-\d{6,}$/', $premier);
        $this->assertMatchesRegularExpression('/^CMD-\d{6,}$/', $second);
        $this->assertGreaterThan(
            (int) substr($premier, 4),
            (int) substr($second, 4),
        );
    }

    public function test_deux_commandes_recoivent_deux_numeros_distincts(): void
    {
        $etablissement = Etablissement::factory()->boutique()->create();
        $client = $etablissement->clients()->create(['nom' => 'Client Test']);
        $produit = Produit::factory()->for($etablissement)->create([
            'quantite_stock' => 10,
            'quantite_reservee' => 0,
        ]);

        $service = app(CreerCommande::class);

        $premiere = $service->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 1)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-numero-1',
        );
        $seconde = $service->executer(
            $etablissement, $client, [new LigneCommandeDemandee($produit->id, null, 1)],
            Canal::Web, SourceCommande::PanierWeb, 'cle-numero-2',
        );

        $this->assertMatchesRegularExpression('/^CMD-\d{6,}$/', $premiere->numero);
        $this->assertNotSame($premiere->numero, $seconde->numero);
    }
}
