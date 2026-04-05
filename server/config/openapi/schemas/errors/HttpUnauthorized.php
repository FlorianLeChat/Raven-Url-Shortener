<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 401],
        'message' => ['type' => 'string', 'example' => 'The API key is missing. Please provide it in the \"Authorization\" HTTP header.']
    ]
];
