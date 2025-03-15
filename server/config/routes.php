<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Paramétrage des routes de l'application.
 * @see https://symfony.com/doc/current/routing.html
 */
return static function (RoutingConfigurator $routes): void
{
	$routes->import(
		['path' => '../src/Action/', 'namespace' => 'App\Action'],
		'attribute',
	);
};