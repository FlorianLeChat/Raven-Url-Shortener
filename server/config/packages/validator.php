<?php

declare(strict_types=1);

use Symfony\Config\FrameworkConfig;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Paramétrage des contraintes de validation de l'application.
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