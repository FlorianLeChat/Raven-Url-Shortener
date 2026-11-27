<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

$cacheDriver = [
    'type' => 'pool',
    'pool' => 'raven_cache_pool',
];

/**
 * @see https://symfony.com/doc/current/doctrine.html
 */
return App::config([
    'doctrine' => [
        'dbal' => [
            'connections' => [
                'default' => [
                    'url' => '%env(DATABASE_TYPE)%://%env(DATABASE_USERNAME)%:%env(DATABASE_PASSWORD)%@%env(DATABASE_HOST)%:%env(DATABASE_PORT)%/%env(DATABASE_NAME)%',
                    'use_savepoints' => true,
                    'server_version' => '%env(DATABASE_VERSION)%',
                    'profiling_collect_backtrace' => param('kernel.debug'),
                ],
            ],
        ],
        'orm' => [
            'entity_managers' => [
                'default' => [
                    'auto_mapping' => true,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'validate_xml_mapping' => true,
                    'mappings' => [
                        'App' => [
                            'dir' => '%kernel.project_dir%/src/Domain/Entity',
                            'alias' => 'App',
                            'prefix' => 'App\\Domain\\Entity',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'when@test' => [
        'doctrine' => [
            'dbal' => [
                'connections' => [
                    'default' => [
                        'url' => 'sqlite:///%kernel.project_dir%/var/data.db',
                        'dbname_suffix' => '_test' . env('TEST_TOKEN')->default(''),
                    ],
                ],
            ],
        ],
    ],
    'when@prod' => [
        'doctrine' => [
            'orm' => [
                'entity_managers' => [
                    'default' => [
                        'metadata_cache_driver' => $cacheDriver,
                        'query_cache_driver' => $cacheDriver,
                        'result_cache_driver' => $cacheDriver,
                    ],
                ],
            ],
        ],
    ],
]);
