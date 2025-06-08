<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la création d'un nouveau lien raccourci.
 */
return [
	'post' => [
		'tags' => ['Link'],
		'summary' => 'Create a new short link',
		'description' => 'Create a new short link, with optional custom slug and expiration date.',
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
			]
		],
		'requestBody' => [
			'content' => [
				'application/json' => [
					'schema' => [
						'type' => 'object',
						'required' => ['url'],
						'properties' => [
							'url' => [
								'type' => 'string',
								'description' => 'The URL to shorten.'
							],
							'slug' => [
								'type' => 'string',
								'description' => 'The custom slug for the short link.'
							],
							'expiration' => [
								'type' => 'string',
								'format' => 'date-time',
								'description' => 'The expiration date for the short link.'
							]
						]
					]
				]
			]
		],
		'responses' => [
			Response::HTTP_CREATED => [
				'description' => 'Link created successfully',
				'content' => [
					'application/json' => [
						'schema' => [
							'type' => 'array',
							'items' => [
								'$ref' => '#/components/schemas/Link'
							]
						]
					]
				]
			],
			Response::HTTP_BAD_REQUEST => [
				'description' => 'Link data validation failed',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpBadRequest'
						]
					]
				]
			],
			Response::HTTP_TOO_MANY_REQUESTS => [
				'description' => 'Link creation rate limit exceeded',
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