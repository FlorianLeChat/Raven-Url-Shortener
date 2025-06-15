<?php

/**
 * Importation des schémas pour OpenAPI.
 */
return [
	// Erreurs HTTP.
	'HttpBadRequest' => require_once __DIR__ . '/errors/HttpBadRequest.php',
	'HttpUnauthorized' => require_once __DIR__ . '/errors/HttpUnauthorized.php',
	'HttpForbidden' => require_once __DIR__ . '/errors/HttpForbidden.php',
	'HttpNotFound' => require_once __DIR__ . '/errors/HttpNotFound.php',
	'HttpConflict' => require_once __DIR__ . '/errors/HttpConflict.php',
	'HttpTooManyRequests' => require_once __DIR__ . '/errors/HttpTooManyRequests.php',
	'HttpInternalServerError' => require_once __DIR__ . '/errors/HttpInternalServerError.php'
];