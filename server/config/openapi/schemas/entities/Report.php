<?php

declare(strict_types=1);

/**
 * Entité pour les signalements de liens raccourcis.
 */
return [
	'type' => 'object',
	'required' => ['link', 'reason', 'createdAt'],
	'properties' => [
		'id' => [
			'title' => 'The unique identifier of the report',
			'type' => 'string',
			'format' => 'uuid',
			'nullable' => true
		],
		'link' => [
			'$ref' => '#/components/schemas/Link'
		],
		'reason' => [
			'title' => 'The reason given for the report',
			'type' => 'string',
			'maxLength' => 500,
			'minLength' => 10
		],
		'email' => [
			'title' => 'The email address of the reporter',
			'type' => 'string',
			'maxLength' => 100,
			'minLength' => 10,
			'nullable' => true
		],
		'createdAt' => [
			'title' => 'The creation date of the report',
			'type' => 'string',
			'format' => 'date-time'
		],
		'updatedAt' => [
			'title' => 'The last update date of the report',
			'type' => 'string',
			'format' => 'date-time',
			'nullable' => true
		]
	]
];