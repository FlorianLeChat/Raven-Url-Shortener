<?php

declare(strict_types=1);

/**
 * Entité pour les clés API de gestion des liens raccourcis.
 */
return [
    'type' => 'object',
    'required' => ['link', 'key', 'createdAt', 'expiresAt'],
    'properties' => [
        'id' => [
            'title' => 'The unique identifier of the API key',
            'type' => 'string',
            'format' => 'uuid'
        ],
        'link' => [
            '$ref' => '#/components/schemas/Link'
        ],
        'key' => [
            'title' => 'The API key',
            'type' => 'string'
        ],
        'createdAt' => [
            'title' => 'The creation date of the API key',
            'type' => 'string',
            'format' => 'date-time'
        ],
        'updatedAt' => [
            'title' => 'The last update date of the API key',
            'type' => 'string',
            'format' => 'date-time',
            'nullable' => true
        ],
        'expiresAt' => [
            'title' => 'The expiration date of the API key',
            'type' => 'string',
            'format' => 'date-time'
        ]
    ]
];
