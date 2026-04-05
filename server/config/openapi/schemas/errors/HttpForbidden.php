<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 403],
        'message' => ['type' => 'string', 'example' => 'The provided API key is invalid. Please check it and try again.']
    ]
];
