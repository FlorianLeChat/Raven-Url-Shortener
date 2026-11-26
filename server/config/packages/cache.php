<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * @see https://symfony.com/doc/current/cache.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void {
    if ($container->env() === 'prod') {
        $redisProvider = 'redis://%env(CACHE_USERNAME)%:%env(CACHE_PASSWORD)%@%env(CACHE_HOST)%/';

        $cache = $framework->cache();
        $cache->app('raven_cache_pool');
        $cache->defaultRedisProvider($redisProvider);
        $cache->pool('raven_cache_pool')
            ->tags(true)
            ->public(true)
            ->adapters([
                ['name' => 'cache.adapter.redis', 'provider' => $redisProvider],
                ['name' => 'cache.adapter.doctrine_dbal'],
                ['name' => 'cache.adapter.filesystem']
            ]);
        $cache->system('cache.adapter.system');
    }
};
