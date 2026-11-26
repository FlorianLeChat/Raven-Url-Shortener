<?php

namespace App\Tests\Action;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests of the `GET /` health check.
 */
final class ServerIndexActionTest extends WebTestCase
{
    private const RESPONSE_TIME_PATTERN = '/Response time: \d+(\.\d+)? ms\./';

    public function testHealthCheck(): void
    {
        $client = self::createClient();
        $client->request('GET', '/', server: ['REQUEST_TIME_FLOAT' => microtime(true)]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/plain; charset=utf-8');
        self::assertMatchesRegularExpression(self::RESPONSE_TIME_PATTERN, (string) $client->getResponse()->getContent());
        self::assertStringContainsString('Version: 0.0.1.', (string) $client->getResponse()->getContent());
    }

    public function testHealthCheckWithoutRequestTime(): void
    {
        $client = self::createClient();
        $client->request('GET', '/', server: ['REQUEST_TIME_FLOAT' => 'invalid']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Server is running.', (string) $client->getResponse()->getContent());
    }
}
