<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/**
 * @see https://symfony.com/doc/current/translation.html
 */
return App::config([
    'framework' => [
        'default_locale' => 'en',
        'enabled_locales' => ['en', 'fr'],
        'set_locale_from_accept_language' => true,
        'set_content_language_from_locale' => true,
        'translator' => [
            'default_path' => '%kernel.project_dir%/locales',
        ],
    ],
]);
