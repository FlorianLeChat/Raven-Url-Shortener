<?php

declare(strict_types=1);

/**
 * Schéma pour l'erreur HTTP 429 (Too Many Requests).
 */
return [
	'type' => 'object',
	'properties' => [
		'code' => ['type' => 'integer', 'example' => 429],
		'message' => ['type' => 'string', 'example' => 'Too many requests made for the current IP address.']
	]
];