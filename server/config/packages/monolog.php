<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;

/**
 * @see https://symfony.com/doc/current/logging.html
 * @see https://symfony.com/doc/current/logging/monolog_email.html
 */
return App::config([
    'monolog' => [
        'handlers' => [
            'main_handler' => [
                'type' => 'fingers_crossed',
                'handler' => 'rotation_handler',
                'action_level' => LogLevel::DEBUG,
                'channels' => [
                    'elements' => ['!event', '!request'],
                ],
                'excluded_http_codes' => [
                    ['code' => 404],
                    ['code' => 405],
                    ['code' => 429],
                ],
            ],
            'rotation_handler' => [
                'type' => 'rotating_file',
                'level' => LogLevel::DEBUG,
                'max_files' => 14,
                'filename_format' => '{date}',
            ],
            'console_handler' => [
                'type' => 'console',
                'process_psr_3_messages' => [
                    'enabled' => false,
                ],
                'channels' => [
                    'elements' => ['!event', '!doctrine'],
                ],
            ],
        ],
    ],
    'when@prod' => [
        'monolog' => [
            'handlers' => [
                'main_handler' => [
                    'buffer_size' => 50,
                    'action_level' => LogLevel::INFO,
                ],
                'rotation_handler' => [
                    'level' => LogLevel::INFO,
                ],
                'critical_handler' => [
                    'type' => 'fingers_crossed',
                    'handler' => 'deduplication_handler',
                    'action_level' => LogLevel::CRITICAL,
                ],
                'deduplication_handler' => [
                    'type' => 'deduplication',
                    'handler' => 'symfony_mailer',
                ],
                'symfony_mailer' => [
                    'type' => 'symfony_mailer',
                    'level' => LogLevel::INFO,
                    'subject' => 'An Error Occurred! %%message%%',
                    'enabled' => env('SMTP_ENABLED')->bool(),
                    'to_email' => ['%env(SMTP_USERNAME)%'],
                    'from_email' => '%env(SMTP_USERNAME)%',
                    'formatter' => 'monolog.formatter.html',
                    'content_type' => 'text/html',
                ],
            ],
        ],
    ],
]);
