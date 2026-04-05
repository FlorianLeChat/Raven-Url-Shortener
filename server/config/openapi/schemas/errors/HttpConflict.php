<?php

declare(strict_types=1);

return [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'integer', 'example' => 409],
        'message' => ['type' => 'string', 'example' => 'You have already reported this shortcut link, you cannot report it again.']
    ]
];
