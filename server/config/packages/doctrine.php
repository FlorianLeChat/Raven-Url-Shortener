<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineConfig;

/**
 * Paramétrage pour l'intégration Doctrine avec Symfony.
 * @see https://symfony.com/doc/current/doctrine.html
 */
return static function (DoctrineConfig $doctrine, ContainerConfigurator $container): void
{
	$runtime = $container->env();
	$dbalConfig = $doctrine->dbal()->connection('default')
		->url('%env(DATABASE_TYPE)%://%env(DATABASE_USERNAME)%:%env(DATABASE_PASSWORD)%@%env(DATABASE_HOST)%:%env(DATABASE_PORT)%/%env(DATABASE_NAME)%')
		->useSavepoints(true)
		->serverVersion('%env(DATABASE_VERSION)%')
		->profilingCollectBacktrace('%kernel.debug%');

	if ($runtime === 'test')
	{
		$dbalConfig
			->url('sqlite:///%kernel.project_dir%/var/data.db')
			->dbnameSuffix('_test' . env('TEST_TOKEN')->default(''));
	}

	$ormConfig = $doctrine->orm();

	$entityManagerConfig = $ormConfig->entityManager('default')
		->autoMapping(true)
		->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware')
		->validateXmlMapping(true);

	$entityManagerConfig->mapping('App')
		->dir(param('kernel.project_dir') . '/src/Domain/Entity')
		->alias('App')
		->prefix('App\\Domain\\Entity');

	if ($runtime === 'prod')
	{
		$entityManagerConfig->metadataCacheDriver()
			->type('pool')
			->pool('raven_cache_pool');

		$entityManagerConfig->queryCacheDriver()
			->type('pool')
			->pool('raven_cache_pool');

		$entityManagerConfig->resultCacheDriver()
			->type('pool')
			->pool('raven_cache_pool');
	}
};