<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage pour le composant Cache de Symfony.
 * @see https://symfony.com/doc/current/cache.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void
{
	$cache = $framework->cache();

	if ($container->env() === 'test')
	{
		$cache->app('cache.adapter.filesystem');
		return;
	}

	$cache->app('raven_cache_pool');
	$cache->system('cache.adapter.system');
	$cache->pool('raven_cache_pool')
		->tags(true)
		->public(true)
		->adapters([
			['name' => 'cache.adapter.redis', 'provider' => 'redis://%env(REDIS_USERNAME)%:%env(REDIS_PASSWORD)%@%env(REDIS_HOST)%:%env(REDIS_PORT)%/'],
			['name' => 'cache.adapter.doctrine_dbal'],
			['name' => 'cache.adapter.filesystem']
		]);
};