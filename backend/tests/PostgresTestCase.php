<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Base des tests de la suite `Postgres` : tout ce qui ne peut être vérifié
 * que sur un vrai moteur PostgreSQL (contraintes CHECK, verrous,
 * transactions). Bascule la connexion par défaut sur `pgsql_testing`
 * (base dédiée `saas_boutiques_test`) avant même que RefreshDatabase ne
 * migre quoi que ce soit, pour que ni les migrations ni les requêtes du
 * test ne touchent la base de développement ni la suite rapide SQLite.
 */
abstract class PostgresTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Bascule la connexion par défaut juste après le boot de l'application,
     * donc avant que RefreshDatabase (déclenché ensuite par setUpTraits())
     * ne migre ou n'ouvre sa transaction de test.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'pgsql_testing');

        return $app;
    }

    protected function migrateFreshUsing(): array
    {
        return [
            '--database' => 'pgsql_testing',
            '--realpath' => true,
        ];
    }
}
