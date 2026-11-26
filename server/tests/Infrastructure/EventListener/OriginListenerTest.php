<?php

namespace App\Tests\Infrastructure\EventListener;

use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\EventListener\OriginListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Tests of OriginListener, for a public and a private API.
 */
final class OriginListenerTest extends TestCase
{
    private const API_KEY = 'private-api-key';

    private const ALLOWED_ORIGIN = 'https://url.domain.com';

    private const PUBLIC_IP = '203.0.113.5';

    private const PRIVATE_IP = '172.16.0.1';

    /**
     * Creates the listener with a translator answering the translation keys.
     *
     * @param string[] $allowedOrigins
     */
    private function createListener(bool $isPrivateApi, array $allowedOrigins = [self::ALLOWED_ORIGIN]): OriginListener
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new OriginListener($isPrivateApi, $allowedOrigins, self::API_KEY, new NullLogger(), $translator);
    }

    /**
     * Runs the listener on the response of a request and returns that response.
     *
     * @param array<string, string> $headers
     */
    private function dispatch(
        OriginListener $listener,
        array $headers = [],
        string $method = 'GET',
        string $ipAddress = self::PUBLIC_IP
    ): Response {
        $request = Request::create('/v1/link', $method, server: ['REMOTE_ADDR' => $ipAddress]);
        $request->headers->add($headers);

        $event = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response(status: Response::HTTP_NO_CONTENT)
        );

        $listener($event);

        return $event->getResponse();
    }

    public function testPublicApiWithoutOrigin(): void
    {
        $response = $this->dispatch($this->createListener(false));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    public function testPublicApiAllowsEveryOrigin(): void
    {
        $response = $this->dispatch($this->createListener(false), ['Origin' => 'https://other.com']);

        self::assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        self::assertSame('Content-Type, Authorization', $response->headers->get('Access-Control-Allow-Headers'));
    }

    public function testPreflightRequest(): void
    {
        $response = $this->dispatch($this->createListener(true), ['Origin' => self::ALLOWED_ORIGIN], 'OPTIONS');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(self::ALLOWED_ORIGIN, $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testPrivateApiWithAllowedOriginAndValidApiKey(): void
    {
        $response = $this->dispatch($this->createListener(true), [
            'Origin' => self::ALLOWED_ORIGIN,
            'Authorization' => 'Bearer ' . self::API_KEY
        ]);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame(self::ALLOWED_ORIGIN, $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testPrivateApiWithWildcardOrigin(): void
    {
        $response = $this->dispatch($this->createListener(true, ['*']), [
            'Origin' => 'https://other.com',
            'Authorization' => 'Bearer ' . self::API_KEY
        ]);

        self::assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testPrivateApiWithUnauthorizedOrigin(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('http.invalid_origin');

        $this->dispatch($this->createListener(true), [
            'Origin' => 'https://other.com',
            'Authorization' => 'Bearer ' . self::API_KEY
        ]);
    }

    public function testPrivateApiFromLocalNetworkWithoutApiKey(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('http.invalid_api_key');

        $this->dispatch($this->createListener(true), ['Origin' => 'https://other.com'], ipAddress: self::PRIVATE_IP);
    }

    public function testPrivateApiWithInvalidApiKey(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('http.invalid_api_key');

        $this->dispatch($this->createListener(true), [
            'Origin' => self::ALLOWED_ORIGIN,
            'Authorization' => 'Bearer wrong-key'
        ]);
    }
}
