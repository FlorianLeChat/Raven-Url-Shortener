<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

/**
 * Paramétrage pour le composant Mailer de Symfony.
 * @see https://symfony.com/doc/current/mailer.html
 */
return static function (FrameworkConfig $framework): void
{
	if ($_ENV['SMTP_ENABLED'] !== 'true')
	{
		return;
	}

	$framework->mailer()
		->dsn('smtp://%env(SMTP_USERNAME)%:%env(SMTP_PASSWORD)%@%env(SMTP_HOST)%:%env(SMTP_PORT)%')
		->header('From', 'Raven Url Shortener <%env(SMTP_USERNAME)%>')
		->envelope()
			->sender('%env(SMTP_USERNAME)%');
};