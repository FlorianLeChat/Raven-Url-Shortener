<?php

declare(strict_types=1);

/**
 * Schéma pour l'erreur HTTP 403 (Forbidden).
 */
return [
	'type' => 'object',
	'properties' => [
		'code' => ['type' => 'integer', 'example' => 403],
		'message' => ['type' => 'string', 'example' => 'The provided API key is invalid. Please check it and try again.']
	]
];