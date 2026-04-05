<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'required' => ['url', 'slug', 'createdAt', 'reports'],
    'properties' => [
        'id' => [
            'title' => 'The unique identifier of the link',
            'type' => 'string',
            'format' => 'uuid'
        ],
        'url' => [
            'title' => 'The shortened URL',
            'type' => 'string'
        ],
        'slug' => [
            'title' => 'The slug of the link',
            'type' => 'string',
            'maxLength' => 50,
            'minLength' => 1,
            'pattern' => '[a-zA-Z0-9-]+'
        ],
        'password' => [
            'title' => 'The password to access the link',
            'type' => 'string',
            'maxLength' => 255,
            'nullable' => true
        ],
        'enabled' => [
            'title' => 'The activation state of the link',
            'type' => 'boolean',
            'default' => true
        ],
        'trusted' => [
            'title' => 'The trust state of the link',
            'type' => 'boolean',
            'default' => false
        ],
        'createdAt' => [
            'title' => 'The creation date of the link',
            'type' => 'string',
            'format' => 'date-time'
        ],
        'updatedAt' => [
            'title' => 'The last update date of the link',
            'type' => 'string',
            'format' => 'date-time',
            'nullable' => true
        ],
        'visitedAt' => [
            'title' => 'The last visit date of the link',
            'type' => 'string',
            'format' => 'date-time',
            'nullable' => true
        ],
        'expiresAt' => [
            'title' => 'The expiration date of the link',
            'type' => 'string',
            'format' => 'date-time',
            'nullable' => true
        ],
        'reports' => [
            'title' => 'The user reports of the link',
            'type' => 'array',
            'items' => [
                '$ref' => '#/components/schemas/Report'
            ],
        ],
        'apiKey' => [
            'title' => 'The API key associated with the link',
            'nullable' => true,
            'oneOf' => [
                ['$ref' => '#/components/schemas/ApiKey']
            ]
        ]
    ]
];
