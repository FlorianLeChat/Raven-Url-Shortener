<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Paramétrage pour le composant Validation de Symfony.
 * @see https://symfony.com/doc/current/validation.html
 */
return static function (FrameworkConfig $framework, string $env): void
{
	$framework->validation()
		->enabled(true)
		->emailValidationMode(Email::VALIDATION_MODE_STRICT);

	if ($env === 'test')
	{
		$framework->validation()->notCompromisedPassword()->enabled(false);
	}
};