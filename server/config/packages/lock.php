<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage pour le composant Lock de Symfony.
 * @see https://symfony.com/doc/current/components/lock.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $container): void
{
	$framework->lock('%env(DATABASE_TYPE)%://%env(DATABASE_USERNAME)%:%env(DATABASE_PASSWORD)%@%env(DATABASE_HOST)%/%env(DATABASE_NAME)%');

	if ($container->env() === 'test')
	{
		$framework->lock('flock');
	}
};
