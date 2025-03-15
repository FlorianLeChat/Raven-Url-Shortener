<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineMigrationsConfig;

/**
 * Paramétrage des migrations Doctrine.
 * @see https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html
 * @see https://www.doctrine-project.org/projects/doctrine-migrations/en/current/reference/configuration.html#configuration
 */
return static function (DoctrineMigrationsConfig $config): void
{
	$config->enableProfiler(false);
	$config->migrationsPath('DoctrineMigrations', '%kernel.project_dir%/migrations');
};