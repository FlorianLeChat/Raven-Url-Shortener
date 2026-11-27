<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The Redis adapter of the chain connects through `default_redis_provider`.
 *
 * @see https://symfony.com/doc/current/cache.html
 */
return App::config([
    'when@prod' => [
        'framework' => [
            'cache' => [
                'app' => 'raven_cache_pool',
                'system' => 'cache.adapter.system',
                'default_redis_provider' => 'redis://%env(CACHE_USERNAME)%:%env(CACHE_PASSWORD)%@%env(CACHE_HOST)%/',
                'pools' => [
                    'raven_cache_pool' => [
                        'tags' => true,
                        'public' => true,
                        'adapters' => [
                            'cache.adapter.redis',
                            'cache.adapter.doctrine_dbal',
                            'cache.adapter.filesystem',
                        ],
                    ],
                ],
            ],
        ],
    ],
]);
