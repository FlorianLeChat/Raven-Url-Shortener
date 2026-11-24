<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * @see https://symfony.com/doc/current/messenger.html
 */
return static function (FrameworkConfig $framework): void {
    $framework->messenger()
        ->bus('messenger.bus.default')
            ->middleware()
                ->id('doctrine_ping_connection');
};
