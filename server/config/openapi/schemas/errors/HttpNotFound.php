<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 404],
        'message' => ['type' => 'string', 'example' => 'The requested resource was not found.']
    ]
];
