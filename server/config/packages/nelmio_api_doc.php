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
		// Schémas de réponse HTTP.
		'schemas' => [
			'HttpBadRequest' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 400],
					'message' => ['type' => 'string', 'example' => 'An error occurred during data validation.'],
					'errors' => [
						'type' => 'object',
						'example' => [
							'url' => [
								[
									'code' => 'INVALID_URL',
									'message' => 'This value is not a valid URL.',
								]
							]
						],
						'additionalProperties' => [
							'type' => 'array',
							'items' => [
								'type' => 'object',
								'properties' => [
									'code' => ['type' => 'string'],
									'message' => ['type' => 'string'],
								],
							],
						],
					],
				],
			],
			'HttpForbidden' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 403],
					'message' => ['type' => 'string', 'example' => 'The provided API key is invalid. Please check it and try again.'],
				],
			],
			'HttpNotFound' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 404],
					'message' => ['type' => 'string', 'example' => 'The requested resource was not found.'],
				],
			],
			'HttpConflict' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 409],
					'message' => ['type' => 'string', 'example' => 'You have already reported this shortcut link, you cannot report it again.'],
				],
			],
			'HttpTooManyRequests' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 429],
					'message' => ['type' => 'string', 'example' => 'Too many requests made for the current IP address.'],
				],
			],
			'HttpInternalServerError' => [
				'type' => 'object',
				'properties' => [
					'code' => ['type' => 'integer', 'example' => 500],
					'message' => ['type' => 'string', 'example' => 'There was an error while processing your request.'],
				],
			],
		],
	]);

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
};