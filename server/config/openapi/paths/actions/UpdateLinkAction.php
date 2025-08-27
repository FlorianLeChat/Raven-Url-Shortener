<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la mise à jour partielle ou complète d'un lien raccourci.
 */
return [
	'patch' => [
		'tags' => ['Link'],
		'summary' => 'Update a specific information of a short link',
		'description' => 'Update a specific information of a short link by its UUID or its custom slug.',
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
				'description' => 'The UUID of the link to update.'
			],
			[
				'in' => 'path',
				'name' => 'slug',
				'schema' => [
					'type' => 'string'
				],
				'required' => false,
				'description' => 'The custom slug of the link to update.'
			]
		],
		'requestBody' => [
			'content' => [
				'application/json' => [
					'schema' => [
						'type' => 'object',
						'required' => ['field', 'value'],
						'properties' => [
							'field' => [
								'type' => 'string',
								'description' => 'The field to update.'
							],
							'value' => [
								'type' => 'string',
								'description' => 'The new value for the field.'
							]
						]
					]
				]
			]
		],
		'responses' => [
			Response::HTTP_OK => [
				'description' => 'Link updated successfully',
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
			Response::HTTP_FORBIDDEN => [
				'description' => 'Link update forbidden',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/HttpForbidden'
						]
					]
				]
			],
			Response::HTTP_TOO_MANY_REQUESTS => [
				'description' => 'Rate limit exceeded',
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
	],
	'put' => [
		'tags' => ['Link'],
		'summary' => 'Update a whole short link',
		'description' => 'Update a whole short link by its UUID or its custom slug.',
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
				'description' => 'The UUID of the link to update.'
			],
			[
				'in' => 'path',
				'name' => 'slug',
				'schema' => [
					'type' => 'string'
				],
				'required' => false,
				'description' => 'The custom slug of the link to update.'
			]
		],
		'requestBody' => [
			'content' => [
				'application/json' => [
					'schema' => [
						'type' => 'object',
						'properties' => [
							'url' => [
								'type' => 'string',
								'description' => 'The URL to shorten.'
							],
							'slug' => [
								'type' => 'string',
								'description' => 'The custom slug for the short link.'
							],
							'password' => [
								'type' => 'string',
								'description' => 'The password to protect the short link.'
							],
							'expiration' => [
								'type' => 'string',
								'format' => 'date-time',
								'description' => 'The expiration date for the short link.'
							],
							'custom-domain' => [
								'type' => 'string',
								'description' => 'The custom domain for the short link.'
							],
							'api-management' => [
								'type' => 'boolean',
								'description' => 'Indicates if the link can be managed using the API.'
							]
						]
					]
				]
			]
		],
		'responses' => [
			Response::HTTP_OK => [
				'description' => 'Link updated successfully',
				'content' => [
					'application/json' => [
						'schema' => [
							'$ref' => '#/components/schemas/Link'
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
			Response::HTTP_FORBIDDEN => [
				'description' => 'Link update forbidden',
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
				'description' => 'Link update rate limit exceeded',
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