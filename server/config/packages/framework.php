<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Tests\Mock\HttpClientResponseFactory;

/**
 * @see https://symfony.com/doc/current/configuration.html
 */
return App::config([
    'framework' => [
        'secret' => '%env(APP_SECRET)%',
        'property_info' => [
            'with_constructor_extractor' => true,
        ],
    ],
    'when@test' => [
        'framework' => [
            'test' => true,
            'http_client' => [
                'mock_response_factory' => HttpClientResponseFactory::class,
            ],
        ],
    ],
    'when@prod' => [
        'parameters' => [
            'app.trusted_proxies' => 'private_ranges',
        ],
        'framework' => [
            'trusted_proxies' => (string) env('APP_TRUSTED_PROXIES')->default('app.trusted_proxies'),
            'trusted_headers' => [
                'x-forwarded-for',
                'x-forwarded-host',
                'x-forwarded-proto',
                'x-forwarded-port',
                'x-forwarded-prefix',
            ],
        ],
    ],
]);
