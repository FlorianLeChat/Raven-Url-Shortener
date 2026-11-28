<?php

namespace App\Tests\Infrastructure\EventListener;

use Throwable;
use RuntimeException;
use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Infrastructure\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Tests of ExceptionListener.
 */
final class ExceptionListenerTest extends TestCase
{
    /**
     * Runs the listener on an exception event and returns the event holding the response.
     */
    private function dispatch(ExceptionListener $listener, Throwable $exception): ExceptionEvent
    {
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $listener($event);

        return $event;
    }

    public function testHttpException(): void
    {
        $listener = new ExceptionListener(new NullLogger(), new IdentityTranslator());
        $event = $this->dispatch($listener, new TooManyRequestsHttpException(30, 'Too many requests'));
        $response = $event->getResponse();

        self::assertNotNull($response);
        self::assertSame(429, $response->getStatusCode());
        self::assertSame('30', $response->headers->get('Retry-After'));
        self::assertSame('application/json', $response->headers->get('Content-Type'));
    }

    public function testResponseIsNotSharedBetweenRequests(): void
    {
        $listener = new ExceptionListener(new NullLogger(), new IdentityTranslator());
        $firstEvent = $this->dispatch($listener, new TooManyRequestsHttpException(30, 'Too many requests'));
        $secondEvent = $this->dispatch($listener, new RuntimeException('Internal error'));
        $secondResponse = $secondEvent->getResponse();

        self::assertNotNull($secondResponse);
        self::assertNotSame($firstEvent->getResponse(), $secondResponse);
        self::assertSame(500, $secondResponse->getStatusCode());
        self::assertFalse($secondResponse->headers->has('Retry-After'));
        self::assertSame('{"code":500,"message":"http.internal_server_error"}', $secondResponse->getContent());
    }
}
