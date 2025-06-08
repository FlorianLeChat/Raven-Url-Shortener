<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la création d'un signalement pour un lien raccourci.
 */
return [
	'post' => [
		'tags' => ['Report'],
		'summary' => 'Create a report for a short link',
		'description' => 'Create a report for a short link by its UUID or its custom slug.',
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
				'description' => 'The UUID of the link to report.'
			],
			[
				'in' => 'path',
				'name' => 'slug',
				'schema' => [
					'type' => 'string'
				],
				'required' => false,
				'description' => 'The custom slug of the link to report.'
			]
		],
		'requestBody' => [
			'content' => [
				'application/json' => [
					'schema' => [
						'type' => 'object',
						'required' => ['reason'],
						'properties' => [
							'reason' => [
								'type' => 'string',
								'description' => 'The reason for reporting the link.'
							],
							'email' => [
								'type' => 'string',
								'format' => 'email',
								'description' => 'The email of the reporter.'
							]
						]
					]
				]
			]
		],
		'responses' => [
			Response::HTTP_CREATED => [
				'description' => 'Report created successfully',
				'content' => [
					'application/json' => [
						'schema' => [
							'type' => 'array',
							'items' => [
								'$ref' => '#/components/schemas/Report'
							]
						]
					]
				]
			],
			Response::HTTP_BAD_REQUEST => [
				'description' => 'Report data validation failed',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpBadRequest'
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
			Response::HTTP_CONFLICT => [
				'description' => 'Report already exists',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpConflict'
						]
					]
				]
			],
			Response::HTTP_TOO_MANY_REQUESTS => [
				'description' => 'Report rate limit exceeded',
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