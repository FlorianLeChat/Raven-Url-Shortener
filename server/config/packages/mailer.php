<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see https://symfony.com/doc/current/mailer.html
 */
return App::config([
    'framework' => [
        'mailer' => [
            'dsn' => 'smtp://%env(SMTP_USERNAME)%:%env(SMTP_PASSWORD)%@%env(SMTP_HOST)%:%env(SMTP_PORT)%',
            'headers' => [
                'From' => 'Raven Url Shortener <%env(SMTP_USERNAME)%>',
            ],
            'enabled' => env('SMTP_ENABLED')->bool(),
            'envelope' => [
                'sender' => '%env(SMTP_USERNAME)%',
            ],
        ],
    ],
]);
