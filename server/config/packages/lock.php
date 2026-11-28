<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * The `captcha` lock marks the solved CAPTCHA challenges: it is never released and expires with the challenge,
 *  so its store must keep a key between requests, honor its lifetime and be shared by every server: the database.
 *
 * @see https://symfony.com/doc/current/components/lock.html
 */
$databaseDsn = '%env(DATABASE_TYPE)%://%env(DATABASE_USERNAME)%:%env(DATABASE_PASSWORD)%@%env(DATABASE_HOST)%'
    . ':%env(DATABASE_PORT)%/%env(DATABASE_NAME)%';

return App::config([
    'framework' => [
        'lock' => [
            'default' => $databaseDsn,
            'captcha' => $databaseDsn,
        ],
    ],
    'when@test' => [
        'framework' => [
            'lock' => [
                'default' => 'flock',
                'captcha' => 'in-memory',
            ],
        ],
    ],
]);
