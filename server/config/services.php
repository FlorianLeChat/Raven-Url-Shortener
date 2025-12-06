<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\Command\UserReportSummary;
use App\Infrastructure\EventListener\OriginListener;
use App\Infrastructure\EventListener\LimiterListener;
use App\Infrastructure\EventListener\ExceptionListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Paramétrage des services de l'application.
 * @see https://symfony.com/doc/current/service_container.html
 */
return static function (ContainerConfigurator $container): void
{
	$services = $container->services()
		->defaults()
			->autowire()
			->autoconfigure();

	$excludes = [
		'../src/Kernel.php',
		'../src/Domain/Entity',
		'../src/DependencyInjection',
	];

	if ($container->env() === 'prod')
	{
		// L'environnement de production n'a pas besoin des données
		//  fictives pour les tests unitaires de PHPUnit.
		$excludes[] = '../src/Infrastructure/Fixture';
	}

	$services->load('App\\', '../src/')
		->exclude($excludes);

	$services->set(UserReportSummary::class)
		->bind('bool $isSmtpEnabled', '%env(bool:SMTP_ENABLED)%')
		->bind('string $smtpUsername', '%env(string:SMTP_USERNAME)%')
		->tag('console.command');

	$services->set(ExceptionListener::class)
		->tag('kernel.event_listener');

	$services->set(OriginListener::class)
		->bind('bool $isPrivateApi', '%env(bool:APP_PRIVATE_API)%')
		->bind('array $allowedOrigins', '%env(csv:APP_PRIVATE_API_ALLOWED_ORIGINS)%')
		->bind('string $apiKey', '%env(string:APP_PRIVATE_API_KEY)%')
		->tag('kernel.event_listener');

	if ($container->env() === 'prod')
	{
		$services->set(LimiterListener::class)
			->bind('bool $isRateLimitEnabled', '%env(bool:APP_RATE_LIMIT_ENABLED)%')
			->tag('kernel.event_listener');
	}
};