<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage pour le composant Routing de Symfony.
 * @see https://symfony.com/doc/current/routing.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void
{
	if ($container->env() === 'prod')
	{
		$framework->router()->strictRequirements(null);
	}
};