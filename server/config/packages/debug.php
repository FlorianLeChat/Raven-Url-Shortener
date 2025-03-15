<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DebugConfig;

/**
 * Paramétrage pour le composant Debug de Symfony.
 * @see https://symfony.com/doc/current/reference/configuration/debug.html
 */
return static function (DebugConfig $config, string $env): void
{
	if ($env === 'dev')
	{
		$config->dumpDestination('tcp://' . env('VAR_DUMPER_SERVER'));
	}
};