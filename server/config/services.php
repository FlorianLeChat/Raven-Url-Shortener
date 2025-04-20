<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Infrastructure\EventListener\CorsListener;
use App\Infrastructure\EventListener\LimiterListener;
use App\Infrastructure\EventListener\ExceptionListener;

/**
 * Paramétrage des services de l'application.
 * @see https://symfony.com/doc/current/service_container.html
 */
return static function (ContainerConfigurator $container, string $env): void
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

	if ($env === 'prod')
	{
		$services->set(LimiterListener::class)
			->tag('kernel.event_listener');
	}
};