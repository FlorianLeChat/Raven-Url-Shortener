<?php

declare(strict_types=1);

/**
 * Schéma pour l'erreur HTTP 404 (Not Found).
 */
return [
	'type' => 'object',
	'properties' => [
		'code' => ['type' => 'integer', 'example' => 404],
		'message' => ['type' => 'string', 'example' => 'The requested resource was not found.']
	]
];