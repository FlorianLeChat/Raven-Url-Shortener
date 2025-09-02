<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

/**
 * Route pour la page d'accueil du serveur.
 */
return [
	'get' => [
		'tags' => ['Server'],
		'summary' => 'Check the server health',
		'description' => 'Check if the server is running and get its response time.',
		'responses' => [
			Response::HTTP_OK => [
				'description' => 'Server is running',
				'content' => [
					'text/plain' => [
						'schema' => [
							'type' => 'string',
							'example' => 'Server is running. Response time: 12.345 ms.'
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