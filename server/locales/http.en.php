<?php

return [
    'too_many_requests' => 'Too many requests made for the current IP address. See response headers for more information.',
    'invalid_origin' => 'Access to the service is denied because the request\'s origin domain is not allowed according to the security policy.',
    'invalid_api_key' => 'Access to the service is denied because an API key is required and was not provided or is invalid. Please provide a valid API key in the "X-Api-Key" HTTP header.',
    'internal_server_error' => 'There was an error while processing your request.',
    'not_found' => 'The requested resource was not found.',
    'unexpected_authorization' => 'This endpoint does not read the "Authorization" HTTP header. Please remove it.'
];
