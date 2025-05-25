<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la suppression d'un lien raccourci.
 */
return [
	'delete' => [
		'tags' => ['Link'],
		'summary' => 'Delete a short link',
		'description' => 'Delete a short link by its UUID or its custom slug.',
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
				'description' => 'The UUID of the link to delete.'
			],
			[
				'in' => 'path',
				'name' => 'slug',
				'schema' => [
					'type' => 'string'
				],
				'required' => false,
				'description' => 'The custom slug of the link to delete.'
			]
		],
		'responses' => [
			Response::HTTP_NO_CONTENT => [
				'description' => 'Link deleted successfully'
			],
			Response::HTTP_BAD_REQUEST => [
				'description' => 'Link data deletion failed',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpBadRequest'
						]
					]
				]
			],
			Response::HTTP_FORBIDDEN => [
				'description' => 'Link deletion forbidden',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpForbidden'
						]
					]
				]
			],
			Response::HTTP_NOT_FOUND => [
				'description' => 'Link not found',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpNotFound'
						]
					]
				]
			],
			Response::HTTP_TOO_MANY_REQUESTS => [
				'description' => 'Link deletion rate limit exceeded',
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
		],
		'security' => [
			['ApiKeyAuth' => []]
		]
	]
];