<?php

namespace App\Tests\Infrastructure\EventListener;

use Psr\Log\NullLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use App\Infrastructure\EventListener\LimiterListener;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Tests of LimiterListener, with in-memory limiters allowing a single request.
 */
final class LimiterListenerTest extends TestCase
{
    private const CLIENT_IP = '203.0.113.5';

    /**
     * Creates a fixed window limiter of one minute, stored in memory.
     */
    private function createLimiter(string $id, int $limit): RateLimiterFactory
    {
        return new RateLimiterFactory([
            'id' => $id,
            'policy' => 'fixed_window',
            'limit' => $limit,
            'interval' => '1 minute'
        ], new InMemoryStorage());
    }

    /**
     * Creates the listener with a translator answering the translation keys.
     */
    private function createListener(bool $isEnabled): LimiterListener
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return new LimiterListener(
            $isEnabled,
            ['en', 'fr'],
            new NullLogger(),
            $this->createLimiter('read', 1),
            $this->createLimiter('write', 1),
            $translator
        );
    }

    /**
     * Runs the listener on a request from a public IP address.
     */
    private function dispatch(LimiterListener $listener, string $method): void
    {
        $request = Request::create('/v1/link', $method, server: ['REMOTE_ADDR' => self::CLIENT_IP]);

        $listener(new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        ));
    }

    public function testDisabledLimiter(): void
    {
        $listener = $this->createListener(false);

        $this->dispatch($listener, 'GET');
        $this->dispatch($listener, 'GET');

        $this->expectNotToPerformAssertions();
    }

    public function testReadAndWriteLimitersAreSeparate(): void
    {
        $listener = $this->createListener(true);

        $this->dispatch($listener, 'GET');
        $this->dispatch($listener, 'POST');

        $this->expectNotToPerformAssertions();
    }

    public function testRateLimitExceeded(): void
    {
        $listener = $this->createListener(true);

        $this->dispatch($listener, 'DELETE');

        try {
            $this->dispatch($listener, 'PATCH');
            self::fail('The rate limit should have been exceeded.');
        } catch (TooManyRequestsHttpException $exception) {
            $headers = $exception->getHeaders();

            self::assertSame('http.too_many_requests', $exception->getMessage());
            self::assertSame(1, $headers['X-RateLimit-Limit']);
            self::assertSame(0, $headers['X-RateLimit-Remaining']);
            self::assertArrayHasKey('X-RateLimit-Reset', $headers);
            self::assertArrayHasKey('Retry-After', $headers);
        }
    }
}
