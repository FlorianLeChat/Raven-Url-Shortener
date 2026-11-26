<?php

namespace App\Tests\Mock;

use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Mocked HTTP responses of the test environment: they replace the requests checking
 *  the shortened URLs, so the tests never reach the network.
 */
final class HttpClientResponseFactory
{
    public const UNREACHABLE_HOST = 'unreachable.example.com';

    public const NOT_FOUND_HOST = 'not-found.example.com';

    public const FORBIDDEN_HOST = 'forbidden.example.com';

    /**
     * Answers the scenario picked by the host of the URL: a transport error, a 404 status,
     *  a 403 status with a firewall page, or a 200 status for any other host.
     */
    public function __invoke(string $method, string $url): MockResponse
    {
        return match (parse_url($url, PHP_URL_HOST)) {
            self::UNREACHABLE_HOST => new MockResponse(info: ['error' => 'Could not resolve host.']),
            self::NOT_FOUND_HOST => new MockResponse(info: ['http_code' => 404]),
            self::FORBIDDEN_HOST => new MockResponse('Access denied', ['http_code' => 403]),
            default => new MockResponse('OK')
        };
    }
}
