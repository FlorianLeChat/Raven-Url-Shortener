<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\NelmioApiDocConfig;

/**
 * Paramétrage pour le composant NelmioApiDocBundle de Symfony.
 * @see https://symfony.com/bundles/NelmioApiDocBundle/current/index.html
 */
return static function (NelmioApiDocConfig $nelmioApiDoc, ContainerConfigurator $container)
{
	// Activation des supports pour les types de données.
	$nelmioApiDoc->typeInfo(true);

	// Définition des zones couvertes par la documentation.
	$nelmioApiDoc->areas('default', [
		'path_patterns' => ['^/api(?!/doc$)'],
	]);

	// Informations générales sur l'API.
	$readLimit = $container->processValue('%env(int:APP_RATE_LIMIT_READ_API)%');
	$writeLimit = $container->processValue('%env(int:APP_RATE_LIMIT_WRITE_API)%');
	$documentation = $nelmioApiDoc->documentation('info', [
		'title' => 'Raven Url Shortener',
		'version' => '1.0.0',
		'description' => 'This is the documentation for the Raven Url Shortener API.<br /><br />
			<strong>By using this service, you accept our legal notice, which can be found <a href="https://url.florian-dev.fr/legal">here</a>.</strong><br />
			All API endpoints are limited in number of requests to prevent abuse and ensure fair use.<br />
			Current values are <strong>' . $readLimit . ' requests per minute</strong> for read operations and <strong>' . $writeLimit . ' requests per minute</strong> for write operations.<br /><br />
			<i>Please note that these limits may evolve according to the service usage and performance constraints.</i><br />
			You are advised to contact an administrator at <q>contact@florian-dev.fr</q> if you wish to exceed these limits for your personal use.
		'
	]);

	// Composants de la documentation.
	$documentation->documentation('components', [
		// Schémas de sécurité.
		'securitySchemes' => [
			'ApiKeyAuth' => [
				'in' => 'header',
				'type' => 'http',
				'scheme' => 'bearer'
			],
			'PasswordAuth' => [
				'in' => 'header',
				'type' => 'apiKey',
				'name' => 'Authorization'
			]
		],

		// Schémas de données.
		'schemas' => require_once __DIR__ . '/../openapi/schemas/bootstrap.php'
	]);

	// Définition des chemins de l'API.
	$nelmioApiDoc->documentation('paths', require_once __DIR__ . '/../openapi/paths/bootstrap.php');

	// Serveurs disponibles
	$documentation->documentation('servers', [
		[
			'url' => 'http://localhost:8000/',
			'description' => 'Development API',
		],
		[
			'url' => 'https://url.florian-dev.fr/',
			'description' => 'Production API',
		],
	]);

	if ($container->env() === 'prod')
	{
		// Mise en cache de la documentation pour la production.
		$nelmioApiDoc->cache([
			'pool' => 'raven_cache_pool',
			'item_id' => 'nelmio_api_doc_caches'
		]);
	}
	else
	{
		// Désactivation du cache en développement.
		$nelmioApiDoc->cache([
			'pool' => null,
			'item_id' => null
		]);
	}
};