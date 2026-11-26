<?php

namespace App\Tests\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\LinkFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Tests of LinkFactory.
 */
final class LinkFactoryTest extends TestCase
{
    public function testCreateLink(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://example.com',
            'slug' => 'example',
            'password' => 'password',
            'expiration' => '2023-12-31'
        ]);

        self::assertSame('https://example.com', $link->getUrl());
        self::assertSame('example', $link->getSlug());
        self::assertStringStartsWith('$2y$', $link->getPassword());
        self::assertTrue($link->isEnabled());
        self::assertFalse($link->isTrusted());
        self::assertNotNull($link->getCreatedAt());
        self::assertNull($link->getUpdatedAt());
        self::assertSame('2023-12-31', $link->getExpiresAt()->format('Y-m-d'));
    }

    public function testCreateLinkWithoutOptionalValues(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'slug' => 'example'
        ]);

        self::assertTrue($link->isTrusted());
        self::assertNull($link->getPassword());
        self::assertNull($link->getExpiresAt());
    }

    public function testUpdateLink(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://my-example.com',
            'slug' => 'example',
            'password' => 'password',
            'expiration' => '2023-12-20'
        ]);

        $link = LinkFactory::update($link, [
            'url' => 'https://updated.com',
            'slug' => 'updated',
            'password' => 'password2',
            'expiration' => null
        ]);

        self::assertSame('https://updated.com', $link->getUrl());
        self::assertSame('updated', $link->getSlug());
        self::assertStringStartsWith('$2y$', $link->getPassword());
        self::assertTrue($link->isEnabled());
        self::assertNotNull($link->getCreatedAt());
        self::assertNotNull($link->getUpdatedAt());
        self::assertNull($link->getExpiresAt());
    }

    public function testPatchLink(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://not-my-example.com',
            'slug' => 'example',
            'password' => 'password',
            'expiration' => '2023-12-30'
        ]);

        $link = LinkFactory::patch($link, 'url', 'https://not-updated.com');

        self::assertSame('https://not-updated.com', $link->getUrl());
        self::assertSame('example', $link->getSlug());
        self::assertStringStartsWith('$2y$', $link->getPassword());
        self::assertTrue($link->isEnabled());
        self::assertNotNull($link->getCreatedAt());
        self::assertNotNull($link->getUpdatedAt());
        self::assertSame('2023-12-30', $link->getExpiresAt()->format('Y-m-d'));
    }

    public function testPatchLinkFields(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://example.com',
            'slug' => 'example'
        ]);

        LinkFactory::patch($link, 'slug', ' patched-slug ');
        LinkFactory::patch($link, 'password', 'new-password');
        LinkFactory::patch($link, 'expiresAt', '2030-06-15');

        self::assertSame('patched-slug', $link->getSlug());
        self::assertTrue(LinkFactory::verifyPassword((string) $link->getPassword(), 'new-password'));
        self::assertFalse(LinkFactory::verifyPassword((string) $link->getPassword(), 'wrong-password'));
        self::assertSame('2030-06-15', $link->getExpiresAt()?->format('Y-m-d'));
    }

    public function testPatchLinkWithUnknownField(): void
    {
        $link = LinkFactory::create([
            'url' => 'https://example.com',
            'slug' => 'example'
        ]);

        $this->expectException(BadRequestHttpException::class);

        LinkFactory::patch($link, 'enabled', 'false');
    }
}
