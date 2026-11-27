<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Validator\Constraints\Email;

/**
 * @see https://symfony.com/doc/current/validation.html
 */
return App::config([
    'framework' => [
        'validation' => [
            'enabled' => true,
            'email_validation_mode' => Email::VALIDATION_MODE_STRICT,
        ],
    ],
    'when@test' => [
        'framework' => [
            'validation' => [
                'not_compromised_password' => [
                    'enabled' => false,
                ],
            ],
        ],
    ],
]);
