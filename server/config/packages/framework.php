<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage général du framework Symfony.
 * @see https://symfony.com/doc/current/configuration.html
 */
return static function (FrameworkConfig $framework, ContainerConfigurator $configuration): void
{
	$runtime = $configuration->env();
	$framework->secret('%env(APP_SECRET)%');

	if ($runtime === 'test')
	{
		$framework->test(true);
		return;
	}

	if ($runtime === 'prod')
	{
		$framework->httpCache()->enabled(true);
	}
};