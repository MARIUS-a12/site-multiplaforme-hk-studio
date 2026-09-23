<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Tests

Deux suites, pour deux besoins différents.

### Suite rapide (SQLite en mémoire) — usage quotidien

Isolation multi-tenant, règles métier (mode de stock, disponibilité), factories, seeder. Aucune dépendance externe, aucun service à démarrer.

```bash
php artisan test
# ou directement :
vendor/bin/phpunit
```

C'est la suite par défaut (`defaultTestSuite="Unit,Feature"` dans `phpunit.xml`) : elle ne lance jamais la suite Postgres, même sans argument.

### Suite `Postgres` — contraintes CHECK, verrous, transactions

Tout ce que SQLite ne peut pas vérifier fidèlement : les contraintes `CHECK` posées en base (ex. `quantite_reservee <= quantite_stock`), et à terme les verrous/transactions concurrentes. Ces tests tournent sur une vraie base PostgreSQL dédiée, `saas_boutiques_test` — jamais sur la base de développement.

Préparation, une seule fois par environnement (utilise les identifiants de la connexion `pgsql` déjà configurée dans `.env`, seul le nom de la base change) :

```bash
php artisan tinker --execute "DB::statement('CREATE DATABASE saas_boutiques_test')"
```

Lancement :

```bash
php artisan test --testsuite=Postgres
```

Cette suite migre `saas_boutiques_test` à la demande (`RefreshDatabase`) et n'est jamais incluse dans un `php artisan test` sans argument — elle a besoin d'un PostgreSQL accessible localement, ce qui n'est pas garanti partout (CI légère, poste sans Postgres, etc.).

### Tout lancer

```bash
php artisan test --testsuite=Unit,Feature,Postgres
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
