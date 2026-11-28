<?php

namespace App\Tests\Infrastructure\EventListener;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use App\Domain\Enum\AccessKeyCapability;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Infrastructure\EventListener\OriginListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\EventListener\AccessKeyListener;
use App\Infrastructure\EventListener\AdminApiKeyListener;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    private function createListener(
        bool $isPrivateApi,
        array $allowedOrigins = [self::ALLOWED_ORIGIN],
        LoggerInterface $logger = new NullLogger(),
        string $apiKey = self::API_KEY
    ): OriginListener {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new OriginListener($isPrivateApi, $allowedOrigins, $apiKey, $logger, $translator);
    }

    /**
     * Runs the listener as the kernel does: on the request, then on the response of the action.
     * A refused request throws before the action, which never runs.
     *
     * @param array<string, string> $headers
     * @param array<string, mixed> $attributes Attributes set by the router and by AdminApiKeyListener.
     */
    private function dispatch(
        OriginListener $listener,
        array $headers = [],
        string $method = 'GET',
        string $ipAddress = self::PUBLIC_IP,
        array $attributes = []
    ): Response {
        $kernel = $this->createStub(HttpKernelInterface::class);
        $request = Request::create('/v1/link', $method, server: ['REMOTE_ADDR' => $ipAddress]);
        $request->headers->add($headers);
        $request->attributes->add($attributes);

        $listener->onKernelRequest(new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST));

        $event = new ResponseEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Response(status: Response::HTTP_NO_CONTENT)
        );

        $listener->onKernelResponse($event);

        return $event->getResponse();
    }

    public function testPublicApiWithoutOrigin(): void
    {
        $response = $this->dispatch($this->createListener(false));

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    public function testPublicApiWithUnexpectedAuthorization(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('unexpected_authorization');

        $this->dispatch($this->createListener(false), ['Authorization' => 'Bearer link-key']);
    }

    public function testPublicApiWithUnknownApiKey(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $this->dispatch($this->createListener(false), ['X-Api-Key' => self::API_KEY]);
    }

    public function testPublicApiAllowsEveryOrigin(): void
    {
        $response = $this->dispatch($this->createListener(false), ['Origin' => 'https://other.com']);

        self::assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        self::assertSame(
            'Content-Type, Authorization, X-Api-Key',
            $response->headers->get('Access-Control-Allow-Headers')
        );
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
            'X-Api-Key' => self::API_KEY
        ]);

        $adminResponse = $this->dispatch(
            $this->createListener(true),
            ['Origin' => self::ALLOWED_ORIGIN, 'Authorization' => 'Bearer admin-api-key'],
            attributes: [AdminApiKeyListener::IS_ADMIN => true]
        );

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertSame(Response::HTTP_NO_CONTENT, $adminResponse->getStatusCode());
        self::assertSame(self::ALLOWED_ORIGIN, $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testPrivateApiWithoutConfiguredApiKey(): void
    {
        $listener = $this->createListener(true, apiKey: '');
        $response = $this->dispatch($listener, ['Origin' => self::ALLOWED_ORIGIN]);

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $this->dispatch($listener, ['Origin' => self::ALLOWED_ORIGIN, 'X-Api-Key' => self::API_KEY]);
    }

    public function testPrivateApiOnLinkCredentialsRoute(): void
    {
        $response = $this->dispatch(
            $this->createListener(true),
            ['Origin' => self::ALLOWED_ORIGIN, 'X-Api-Key' => self::API_KEY, 'Authorization' => 'Bearer link-key'],
            'PATCH',
            attributes: [OriginListener::CREDENTIALS_ROUTE => OriginListener::LINK_API_KEY]
        );

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $this->dispatch(
            $this->createListener(true),
            ['Origin' => self::ALLOWED_ORIGIN, 'Authorization' => 'Password link-password'],
            attributes: [OriginListener::CREDENTIALS_ROUTE => OriginListener::LINK_PASSWORD]
        );
    }

    public function testPrivateApiWithAccessKey(): void
    {
        $keyOrigin = 'https://partner.com';
        $privateAccess = [
            AccessKeyListener::CAPABILITIES => [AccessKeyCapability::PrivateApi->value],
            AccessKeyListener::ALLOWED_ORIGINS => [$keyOrigin]
        ];

        $scriptResponse = $this->dispatch(
            $this->createListener(true),
            ['X-Api-Key' => 'access-key'],
            attributes: $privateAccess
        );
        $browserResponse = $this->dispatch(
            $this->createListener(true),
            ['Origin' => $keyOrigin, 'X-Api-Key' => 'access-key'],
            attributes: $privateAccess
        );
        $preflightResponse = $this->dispatch(
            $this->createListener(true),
            ['Origin' => $keyOrigin],
            'OPTIONS',
            attributes: [AccessKeyListener::ALLOWED_ORIGINS => [$keyOrigin]]
        );

        self::assertSame(Response::HTTP_NO_CONTENT, $scriptResponse->getStatusCode());
        self::assertSame($keyOrigin, $browserResponse->headers->get('Access-Control-Allow-Origin'));
        self::assertSame(['Origin'], $browserResponse->getVary());
        self::assertSame($keyOrigin, $preflightResponse->headers->get('Access-Control-Allow-Origin'));

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $this->dispatch(
            $this->createListener(true),
            ['Origin' => self::ALLOWED_ORIGIN, 'X-Api-Key' => 'access-key'],
            attributes: [AccessKeyListener::CAPABILITIES => [AccessKeyCapability::TrustedLinks->value]]
        );
    }

    public function testPrivateApiWithAccessKeyFromUndeclaredOrigin(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_origin');

        $this->dispatch(
            $this->createListener(true),
            ['Origin' => 'https://other.com', 'X-Api-Key' => 'access-key'],
            attributes: [AccessKeyListener::CAPABILITIES => [AccessKeyCapability::PrivateApi->value]]
        );
    }

    public function testPrivateApiWithWildcardOrigin(): void
    {
        $response = $this->dispatch($this->createListener(true, ['*']), [
            'Origin' => 'https://other.com',
            'X-Api-Key' => self::API_KEY
        ]);

        self::assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }

    public function testPrivateApiWithUnauthorizedOrigin(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_origin');

        $this->dispatch($this->createListener(true), [
            'Origin' => 'https://other.com',
            'X-Api-Key' => self::API_KEY
        ]);
    }

    public function testPrivateApiFromLocalNetworkWithoutApiKey(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $this->dispatch($this->createListener(true), ['Origin' => 'https://other.com'], ipAddress: self::PRIVATE_IP);
    }

    public function testPrivateApiWithInvalidApiKey(): void
    {
        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('invalid_api_key');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with(
            self::logicalNot(self::stringContains('wrong-key')),
            ['apiKey' => 'an invalid']
        );

        $this->dispatch($this->createListener(true, logger: $logger), [
            'Origin' => self::ALLOWED_ORIGIN,
            'X-Api-Key' => 'wrong-key'
        ]);
    }
}
