<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 500],
        'message' => ['type' => 'string', 'example' => 'There was an error while processing your request.']
    ]
];
