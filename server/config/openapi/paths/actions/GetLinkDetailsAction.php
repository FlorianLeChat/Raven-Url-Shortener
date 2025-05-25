<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la récupération des informations d'un lien raccourci.
 */
return [
	'get' => [
		'tags' => ['Link'],
		'summary' => 'Get a short link details',
		'description' => 'Get the details of a short link by its UUID or its custom slug.',
		'parameters' => [
			[
				'in' => 'path',
				'name' => 'version',
				'schema' => [
					'type' => 'string',
					'default' => '1'
				],
				'required' => true,
				'description' => 'The API version to use.'
			],
			[
				'in' => 'path',
				'name' => 'id',
				'schema' => [
					'type' => 'string',
					'format' => 'uuid'
				],
				'required' => false,
				'description' => 'The UUID of the link to fetch.'
			],
			[
				'in' => 'path',
				'name' => 'slug',
				'schema' => [
					'type' => 'string'
				],
				'required' => false,
				'description' => 'The custom slug of the link to fetch.'
			]
		],
		'responses' => [
			Response::HTTP_OK => [
				'description' => 'Link details retrieved successfully',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/Link'
						]
					]
				]
			],
			Response::HTTP_NOT_FOUND => [
				'description' => 'Link not found',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpBadRequest'
						]
					]
				]
			],
			Response::HTTP_TOO_MANY_REQUESTS => [
				'description' => 'Link fetch rate limit exceeded',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpTooManyRequests'
						]
					]
				]
			],
			Response::HTTP_INTERNAL_SERVER_ERROR => [
				'description' => 'Internal server error',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpInternalServerError'
						]
					]
				]
			]
		]
	]
];