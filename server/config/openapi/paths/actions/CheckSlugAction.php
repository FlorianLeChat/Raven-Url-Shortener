<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la vérification de la disponibilité d'un slug personnalisé.
 */
return [
	'post' => [
		'tags' => ['Slug'],
		'summary' => 'Check slug availability',
		'description' => 'Check if a custom slug is available.',
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
						'required' => ['slug'],
						'properties' => [
							'slug' => [
								'type' => 'string',
								'description' => 'The slug to check for availability.'
							]
						]
					]
				]
			]
		],
		'responses' => [
			Response::HTTP_OK => [
				'description' => 'Slug is available or not',
				'content' => [
					'application/json' => [
						'schema' => [
							'type' => 'object',
							'properties' => [
								'available' => [
									'type' => 'boolean',
									'description' => 'True if the slug is available, false otherwise.'
								]
							]
						]
					]
				]
			]
		]
	]
];