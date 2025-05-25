<?php

declare(strict_types=1);

/**
 * Schéma pour l'erreur HTTP 400 (Bad Request).
 */
return [
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
						'message' => 'This value is not a valid URL.'
					]
				]
			],
			'additionalProperties' => [
				'type' => 'array',
				'items' => [
					'type' => 'object',
					'properties' => [
						'code' => ['type' => 'string'],
						'message' => ['type' => 'string']
					]
				]
			]
		]
	]
];