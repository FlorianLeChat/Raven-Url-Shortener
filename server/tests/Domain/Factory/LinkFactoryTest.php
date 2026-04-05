<?php

namespace App\Tests\Domain\Factory;

use Exception;
use PHPUnit\Framework\TestCase;
use App\Domain\Factory\LinkFactory;

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

        $this->assertEquals('https://example.com', $link->getUrl());
        $this->assertEquals('example', $link->getSlug());
        $this->assertStringStartsWith('$2y$', $link->getPassword());
        $this->assertTrue($link->isEnabled());
        $this->assertNotNull($link->getCreatedAt());
        $this->assertNull($link->getUpdatedAt());
        $this->assertEquals('2023-12-31', $link->getExpiresAt()->format('Y-m-d'));
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

        $this->assertEquals('https://updated.com', $link->getUrl());
        $this->assertEquals('updated', $link->getSlug());
        $this->assertStringStartsWith('$2y$', $link->getPassword());
        $this->assertTrue($link->isEnabled());
        $this->assertNotNull($link->getCreatedAt());
        $this->assertNotNull($link->getUpdatedAt());
        $this->assertNull($link->getExpiresAt());
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

        $this->assertEquals('https://not-updated.com', $link->getUrl());
        $this->assertEquals('example', $link->getSlug());
        $this->assertStringStartsWith('$2y$', $link->getPassword());
        $this->assertTrue($link->isEnabled());
        $this->assertNotNull($link->getCreatedAt());
        $this->assertNotNull($link->getUpdatedAt());
        $this->assertEquals('2023-12-30', $link->getExpiresAt()->format('Y-m-d'));
    }
}
