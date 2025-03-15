<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage général du framework Symfony.
 * @see https://symfony.com/doc/current/configuration.html
 */
return static function (FrameworkConfig $framework, string $env): void
{
	$framework->secret('%env(APP_SECRET)%');

	if ($env === 'test')
	{
		$framework->test(true);
		return;
	}

	if ($env === 'prod')
	{
		$framework->httpCache()->enabled(true);
	}
};