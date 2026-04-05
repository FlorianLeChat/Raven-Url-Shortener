<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * @see https://symfony.com/doc/current/rate_limiter.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void {
    if ($container->env() === 'prod') {
        $limiter = $framework->rateLimiter();

        $limiter->limiter('read_api')
            ->limit('%env(int:APP_RATE_LIMIT_READ_API)%')
            ->policy('sliding_window')
            ->interval('1 minute')
            ->cachePool('raven_cache_pool');

        $limiter->limiter('write_api')
            ->limit('%env(int:APP_RATE_LIMIT_WRITE_API)%')
            ->policy('sliding_window')
            ->interval('1 minute')
            ->cachePool('raven_cache_pool');
    }
};
