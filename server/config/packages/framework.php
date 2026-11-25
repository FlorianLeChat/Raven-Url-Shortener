<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * @see https://symfony.com/doc/current/configuration.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void {
    $runtime = $container->env();
    $framework->secret('%env(APP_SECRET)%');
    $framework->propertyInfo()->withConstructorExtractor(true);

    if ($runtime === 'test') {
        $framework->test(true);
        return;
    }

    if ($runtime === 'prod') {
        $container->parameters()->set('app.trusted_proxies', 'private_ranges');
        $framework->trustedProxies((string) env('APP_TRUSTED_PROXIES')->default('app.trusted_proxies'));
        $framework->trustedHeaders([
            'x-forwarded-for',
            'x-forwarded-host',
            'x-forwarded-proto',
            'x-forwarded-port',
            'x-forwarded-prefix',
        ]);
    }
};
