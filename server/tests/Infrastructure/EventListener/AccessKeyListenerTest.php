<?php

namespace App\Tests\Infrastructure\EventListener;

use Psr\Log\NullLogger;
use App\Tests\TestHelperTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Security\AccessKeyCache;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use App\Infrastructure\EventListener\AccessKeyListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests of AccessKeyListener for a private API, on the keys of AccessKeyFixture.
 */
final class AccessKeyListenerTest extends KernelTestCase
{
    use TestHelperTrait;

    private AccessKeyListener $listener;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = self::getContainer();

        self::loadFixtures($kernel->getProjectDir());

        $container->get(CacheItemPoolInterface::class)->deleteItem('access_key_origins');

        $this->listener = new AccessKeyListener(true, '', new NullLogger(), $container->get(AccessKeyCache::class));
    }

    /**
     * Runs the listener and answers the origins it allowed.
     *
     * @param array<string, string> $headers
     * @return string[]
     */
    private function getAllowedOrigins(string $method, array $headers): array
    {
        $request = Request::create('/v1/link', $method);
        $request->headers->add($headers);

        $this->listener->onKernelRequest(new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        ));

        return AccessKeyListener::getAllowedOrigins($request);
    }

    public function testPreflightFromDeclaredOrigins(): void
    {
        $fullOrigin = 'https://full.example.com';

        self::assertSame([$fullOrigin], $this->getAllowedOrigins('OPTIONS', ['Origin' => $fullOrigin]));
        self::assertSame([], $this->getAllowedOrigins('OPTIONS', ['Origin' => 'https://trusted.example.com']));
        self::assertSame([], $this->getAllowedOrigins('OPTIONS', ['Origin' => 'https://expired.example.com']));
        self::assertSame([], $this->getAllowedOrigins('OPTIONS', []));
    }

    public function testAccessKeyOrigins(): void
    {
        $fullKey = ['X-Api-Key' => 'ysvT1q67SCh7h+jAqC/gbrISbIMsuCBONZL4sK8/Urs='];
        $trustedKey = ['X-Api-Key' => '91NknkOqck0+Jv0/7NUBBfixUKEjPwfY4ADVG4EmJpw='];

        self::assertSame(['https://full.example.com'], $this->getAllowedOrigins('GET', $fullKey));
        self::assertSame([], $this->getAllowedOrigins('GET', $trustedKey));
    }
}
