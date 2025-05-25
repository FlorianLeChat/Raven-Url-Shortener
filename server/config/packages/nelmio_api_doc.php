<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\NelmioApiDocConfig;

/**
 * Paramétrage pour le composant NelmioApiDocBundle de Symfony.
 * @see https://symfony.com/bundles/NelmioApiDocBundle/current/index.html
 */
return static function (NelmioApiDocConfig $nelmioApiDoc, string $env)
{
    // Activation des supports pour les types de données.
    $nelmioApiDoc->typeInfo(true);

    // Définition des zones couvertes par la documentation.
	$nelmioApiDoc->areas('default', [
		'path_patterns' => ['^/api(?!/doc$)'],
	]);

    // Informations générales sur l'API.
    $documentation = $nelmioApiDoc->documentation('info', [
		'title' => 'Raven Url Shortener',
		'description' => 'This is the API documentation for the Raven Url Shortener.',
		'version' => '1.0.0',
	]);

	// Composants de la documentation.
	$documentation->documentation('components', [
		// Schémas de sécurité.
		'securitySchemes' => [
			'ApiKeyAuth' => [
				'type' => 'apiKey',
				'in' => 'header',
				'name' => 'Authorization',
			],
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

	if ($env === 'prod')
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