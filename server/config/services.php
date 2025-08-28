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

	$services->load('App\\', '../src/')
		->exclude('../src/{DependencyInjection,Domain/Entity,Kernel.php}');

	$services->set(UserReportSummary::class)
		->bind('bool $isSmtpEnabled', '%env(bool:SMTP_ENABLED)%')
		->bind('bool $isDkimEnabled', '%env(bool:DKIM_ENABLED)%')
		->bind('string $smtpUsername', '%env(string:SMTP_USERNAME)%')
		->bind('string $dkimPrivateKey', '%env(string:DKIM_PRIVATE_KEY)%')
		->bind('string $dkimSelector', '%env(string:DKIM_SELECTOR)%')
		->bind('string $dkimDomain', '%env(string:DKIM_DOMAIN)%')
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