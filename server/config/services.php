<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\EventListener\CorsListener;
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

	$services->set(ExceptionListener::class)
		->tag('kernel.event_listener');

	$services->set(CorsListener::class)
		->tag('kernel.event_listener');

	$container->parameters()
		->set('smtp.enabled', '%env(bool:SMTP_ENABLED)%')
		->set('smtp.username', '%env(string:SMTP_USERNAME)%')
		->set('app.allowed_origins', '%env(csv:APP_ALLOWED_ORIGINS)%')
		->set('dkim.enabled', '%env(bool:DKIM_ENABLED)%')
		->set('dkim.private_key', '%env(string:DKIM_PRIVATE_KEY)%')
		->set('dkim.selector', '%env(string:DKIM_SELECTOR)%')
		->set('dkim.domain', '%env(string:DKIM_DOMAIN)%');

	if ($container->env() === 'prod')
	{
		$services->set(LimiterListener::class)
			->tag('kernel.event_listener');
	}
};