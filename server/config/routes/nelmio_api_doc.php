<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Paramétrage pour le composant NelmioApiDocBundle de Symfony.
 * @see https://symfony.com/bundles/NelmioApiDocBundle/current/index.html
 */
return static function (RoutingConfigurator $routes): void
{
	$routes->add('nelmio_api_doc.swagger', '/api/swagger.json')
		->methods(['GET'])
		->defaults(['_controller' => 'nelmio_api_doc.controller.swagger']);
};