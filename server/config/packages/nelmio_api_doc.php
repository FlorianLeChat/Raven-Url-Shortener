<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

$version = '%env(VERSION)%';
$readLimit = '%env(int:APP_RATE_LIMIT_READ_API)%';
$writeLimit = '%env(int:APP_RATE_LIMIT_WRITE_API)%';
$isRateLimiting = '%env(APP_RATE_LIMIT_ENABLED)%';

/**
 * @see https://symfony.com/bundles/NelmioApiDocBundle/current/index.html
 */
return App::config([
    'nelmio_api_doc' => [
        'type_info' => true,
        'areas' => [
            'default' => [
                'path_patterns' => ['^/api(?!/doc$)'],
            ],
        ],
        'documentation' => [
            'info' => [
                'title' => 'Raven Url Shortener',
                'version' => $version,
                'description' => 'This is the documentation for the Raven Url Shortener API.<br /><br />
            <strong>By using this service, you accept our legal notice, which can be found <a href="https://url.florian-dev.fr/legal">here</a>.</strong><br />
            All API endpoints can be limited (currently: <strong>' . $isRateLimiting . '</strong>) in number of requests to prevent abuse and ensure fair use.<br />
            Current values are <strong>' . $readLimit . ' requests per minute</strong> for read operations and <strong>' . $writeLimit . ' requests per minute</strong> for write operations.<br /><br />
            <i>Please note that these limits may evolve according to the service usage and performance constraints.</i><br />
            You are advised to contact an administrator at <q>contact@florian-dev.fr</q> if you wish to exceed these limits for your personal use.
        ',
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'in' => 'header',
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ],
                    'PasswordAuth' => [
                        'in' => 'header',
                        'type' => 'apiKey',
                        'name' => 'Authorization',
                    ],
                ],
                'schemas' => require __DIR__ . '/../openapi/schemas/bootstrap.php',
            ],
            'paths' => require __DIR__ . '/../openapi/paths/bootstrap.php',
            'servers' => [
                [
                    'url' => 'http://localhost:8000/',
                    'description' => 'Development API',
                ],
                [
                    'url' => 'https://api.url.florian-dev.fr/',
                    'description' => 'Production API',
                ],
            ],
        ],
        'cache' => [
            'pool' => null,
            'item_id' => null,
        ],
    ],
    'when@prod' => [
        'nelmio_api_doc' => [
            'cache' => [
                'pool' => 'raven_cache_pool',
                'item_id' => 'nelmio_api_doc_caches_' . $version,
            ],
        ],
    ],
]);
