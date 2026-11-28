<?php

namespace App\Tests\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use PHPUnit\Framework\TestCase;
use App\Domain\Factory\LinkApiKeyFactory;

/**
 * Tests of LinkApiKeyFactory.
 */
final class LinkApiKeyFactoryTest extends TestCase
{
    public function testCreateApiKey(): void
    {
        $apiKey = LinkApiKeyFactory::create($this->createMock(Link::class));

        self::assertSame(44, strlen($apiKey->getPlainKey()));
        self::assertSame(hash('sha256', $apiKey->getPlainKey()), $apiKey->getKey());
        self::assertNotNull($apiKey->getCreatedAt());
        self::assertNull($apiKey->getUpdatedAt());
        self::assertLessThan((new DateTimeImmutable())->modify('+3 months'), $apiKey->getExpiresAt());
    }

    public function testRotateApiKey(): void
    {
        $apiKey = LinkApiKeyFactory::create($this->createMock(Link::class));
        $plainKey = $apiKey->getPlainKey();

        self::assertNotEquals($apiKey->getKey(), LinkApiKeyFactory::rotate($apiKey)->getKey());
        self::assertNotSame(hash('sha256', $plainKey), $apiKey->getKey());
        self::assertNotEquals($apiKey->getExpiresAt(), LinkApiKeyFactory::rotate($apiKey)->getExpiresAt());
        self::assertNotNull($apiKey->getUpdatedAt());
    }
}
