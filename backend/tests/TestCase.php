<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /**
     * Aucun test ne doit jamais toucher un vrai réseau — le seul appel
     * sortant de toute l'application est la vérification pwnedpasswords.com
     * de Password::uncompromised() (voir PwnedPasswordVerifier).
     * preventStrayRequests() transforme tout appel non explicitement simulé
     * (par un Http::fake() posé DANS le test) en échec immédiat
     * (StrayRequestException) plutôt qu'un vrai aller-retour réseau : une
     * suite qui passe est donc la preuve qu'aucun test n'en a fait un, pas
     * une simple absence de chance.
     *
     * Volontairement AUCUN Http::fake() par défaut ici : PwnedPasswordVerifier
     * traite déjà toute exception (StrayRequestException y compris) comme
     * "service indisponible" et laisse passer le mot de passe — exactement
     * le comportement de repli voulu pour tout test qui ne s'intéresse pas
     * spécifiquement à ce service. Un test qui veut un scénario précis
     * (compromis, sain, service indisponible) pose son propre Http::fake().
     */
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }
}
