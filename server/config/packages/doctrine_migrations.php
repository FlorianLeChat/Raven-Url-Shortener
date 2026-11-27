<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html
 * @see https://www.doctrine-project.org/projects/doctrine-migrations/en/current/reference/configuration.html#configuration
 */
return App::config([
    'doctrine_migrations' => [
        'enable_profiler' => false,
        'migrations_paths' => [
            'DoctrineMigrations' => '%kernel.project_dir%/migrations',
        ],
    ],
]);
