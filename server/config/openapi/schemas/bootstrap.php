<?php

return [
    'HttpBadRequest' => require __DIR__ . '/errors/HttpBadRequest.php',
    'HttpUnauthorized' => require __DIR__ . '/errors/HttpUnauthorized.php',
    'HttpForbidden' => require __DIR__ . '/errors/HttpForbidden.php',
    'HttpNotFound' => require __DIR__ . '/errors/HttpNotFound.php',
    'HttpConflict' => require __DIR__ . '/errors/HttpConflict.php',
    'HttpTooManyRequests' => require __DIR__ . '/errors/HttpTooManyRequests.php',
    'HttpInternalServerError' => require __DIR__ . '/errors/HttpInternalServerError.php',
    'Link' => require __DIR__ . '/entities/Link.php',
    'Report' => require __DIR__ . '/entities/Report.php',
    'ApiKey' => require __DIR__ . '/entities/ApiKey.php'
];
