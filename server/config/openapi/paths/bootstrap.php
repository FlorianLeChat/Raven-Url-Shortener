<?php

/**
 * Importation des routes pour OpenAPI.
 */
return [
	'/api' => require __DIR__ . '/actions/ServerIndexAction.php',
	'/api/v{version}/slug' => require __DIR__ . '/actions/CheckSlugAction.php',
	'/api/v{version}/link' => require __DIR__ . '/actions/CreateLinkAction.php',
	'/api/v{version}/link/{id}' => array_merge(
		require __DIR__ . '/actions/GetLinkDetailsAction.php',
		require __DIR__ . '/actions/UpdateLinkAction.php',
		require __DIR__ . '/actions/DeleteLinkAction.php'
	),
	'/api/v{version}/link/{slug}' => array_merge(
		require __DIR__ . '/actions/GetLinkDetailsAction.php',
		require __DIR__ . '/actions/UpdateLinkAction.php',
		require __DIR__ . '/actions/DeleteLinkAction.php'
	),
	'/api/v{version}/link/{id}/report' => require __DIR__ . '/actions/ReportLinkAction.php',
	'/api/v{version}/link/{slug}/report' => require __DIR__ . '/actions/ReportLinkAction.php'
];