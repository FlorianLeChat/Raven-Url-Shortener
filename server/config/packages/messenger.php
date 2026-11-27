<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see https://symfony.com/doc/current/messenger.html
 */
return App::config([
    'framework' => [
        'messenger' => [
            'buses' => [
                'messenger.bus.default' => [
                    'middleware' => ['doctrine_ping_connection'],
                ],
            ],
        ],
    ],
]);
