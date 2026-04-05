<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 429],
        'message' => ['type' => 'string', 'example' => 'Too many requests made for the current IP address.']
    ]
];
