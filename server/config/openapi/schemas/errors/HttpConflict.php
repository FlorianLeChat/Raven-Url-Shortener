<?php

declare(strict_types=1);

/**
 * Schéma pour l'erreur HTTP 409 (Conflict).
 */
return [
	'type' => 'object',
	'properties' => [
		'code' => ['type' => 'integer', 'example' => 409],
		'message' => ['type' => 'string', 'example' => 'You have already reported this shortcut link, you cannot report it again.']
	]
];