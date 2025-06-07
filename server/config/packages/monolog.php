<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

/**
 * Paramétrage pour le composant Logging de Symfony.
 * @see https://symfony.com/doc/current/logging.html
 */
return static function (MonologConfig $monolog, ContainerConfigurator $container): void
{
	// Capture des événements sur tous les canaux.
	$mainHandler = $monolog->handler('main_handler')
		->type('fingers_crossed')
		->handler('rotation_handler')
		->actionLevel(LogLevel::DEBUG);

	$mainHandler->channels()
		->elements((['!event', '!request']));

	$mainHandler->excludedHttpCode()
		->code(404)
		->code(405)
		->code(429);

	// Rotation des journaux des événements.
	$rotationHandler = $monolog->handler('rotation_handler')
		->type('rotating_file')
		->level(LogLevel::DEBUG)
		->maxFiles(14)
		->filenameFormat('{date}');

	// Journalisation de la sortie standard.
	$monologHandler = $monolog->handler('console_handler')
		->type('console')
		->processPsr3Messages(false);

	$monologHandler->channels()
		->elements(['!event', '!doctrine']);

	if ($container->env() === 'prod')
	{
		// Ajout d'une mémoire tampon pour les messages de journalisation
		//  et modification du niveau d'action.
		$mainHandler->bufferSize(50);
		$mainHandler->actionLevel(LogLevel::INFO);

		$rotationHandler->level(LogLevel::INFO);

		// Envoi des erreurs critiques par courriel avec déduplication.
		// Source : https://symfony.com/doc/current/logging/monolog_email.html
		$monolog->handler('critical_handler')
			->type('fingers_crossed')
			->handler('deduplication_handler')
			->actionLevel(LogLevel::CRITICAL);

		$monolog->handler('deduplication_handler')
			->type('deduplication')
			->handler('symfony_mailer');

		$monolog->handler('symfony_mailer')
			->type('symfony_mailer')
			->level(LogLevel::INFO)
			->subject('An Error Occurred! %%message%%')
			->toEmail('%env(SMTP_USERNAME)%')
			->fromEmail('%env(SMTP_USERNAME)%')
			->formatter('monolog.formatter.html')
			->contentType('text/html');
	}
};