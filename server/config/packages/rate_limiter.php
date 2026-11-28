<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

$limiterPolicy = [
    'policy' => 'sliding_window',
    'interval' => '1 minute',
    'cache_pool' => 'app.shared_cache_pool',
];

/**
 * @see https://symfony.com/doc/current/rate_limiter.html
 */
return App::config([
    'when@prod' => [
        'framework' => [
            'rate_limiter' => [
                'read_api' => ['limit' => '%env(int:RATE_LIMIT_READ_API)%'] + $limiterPolicy,
                'write_api' => ['limit' => '%env(int:RATE_LIMIT_WRITE_API)%'] + $limiterPolicy,
            ],
        ],
    ],
]);
