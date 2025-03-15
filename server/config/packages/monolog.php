<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

/**
 * Paramétrage pour le composant Logging de Symfony.
 * @see https://symfony.com/doc/current/logging.html
 */
return static function (MonologConfig $monolog, string $env): void
{
	// Capture des événements sur tous les canaux.
	$mainHandler = $monolog->handler('main')
		->type('fingers_crossed')
		->handler('rotation')
		->actionLevel(LogLevel::DEBUG);

	$mainHandler->channels()
		->elements((['!event', '!request']));

	$mainHandler->excludedHttpCode()
		->code(404)
		->code(405)
		->code(429);

	// Rotation des journaux des événements.
	$rotationHandler = $monolog->handler('rotation')
		->type('rotating_file')
		->level(LogLevel::DEBUG)
		->maxFiles(14)
		->filenameFormat('{date}');

	// Journalisation de la sortie standard.
	$monologHandler = $monolog->handler('console')
		->type('console')
		->processPsr3Messages(false);

	$monologHandler->channels()
		->elements(['!event', '!doctrine']);

	if ($env === 'prod')
	{
		// En production, on déclenche l'enregistrement des journaux
		//  uniquement lorsqu'une erreur survient.
		$mainHandler->bufferSize(50);
		$mainHandler->actionLevel(LogLevel::ERROR);

		if ($_ENV['SMTP_ENABLED'] === 'true')
		{
			// Si le serveur SMTP est activé, on envoie procède à la
			//  déduplication des messages et on les envoie par courriel.
			// Source : https://symfony.com/doc/current/logging/monolog_email.html
			$mainHandler->handler('grouped');

			$monolog->handler('grouped')
				->type('group')
				->members(['rotation', 'deduplication']);

			$rotationHandler->level(LogLevel::INFO);

			$monolog->handler('deduplication')
				->type('deduplication')
				->time(300)
				->handler('symfony_mailer');

			$monolog->handler('symfony_mailer')
				->type('symfony_mailer')
				->level(LogLevel::DEBUG)
				->subject('An Error Occurred! %%message%%')
				->toEmail('%env(SMTP_USERNAME)%')
				->fromEmail('%env(SMTP_USERNAME)%')
				->contentType('text/html');
		}
	}
};