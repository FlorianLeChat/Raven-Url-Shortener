<?php

namespace App\Tests\Domain\Factory;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ApiKeyFactory;

/**
 * Tests of ApiKeyFactory.
 */
final class ApiKeyFactoryTest extends TestCase
{
    public function testCreateApiKey(): void
    {
        $apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));

        self::assertSame(44, strlen($apiKey->getPlainKey()));
        self::assertSame(hash('sha256', $apiKey->getPlainKey()), $apiKey->getKey());
        self::assertNotNull($apiKey->getCreatedAt());
        self::assertNull($apiKey->getUpdatedAt());
        self::assertLessThan((new DateTimeImmutable())->modify('+3 months'), $apiKey->getExpiresAt());
    }

    public function testRotateApiKey(): void
    {
        $apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));
        $plainKey = $apiKey->getPlainKey();

        self::assertNotEquals($apiKey->getKey(), ApiKeyFactory::rotate($apiKey)->getKey());
        self::assertNotSame(hash('sha256', $plainKey), $apiKey->getKey());
        self::assertNotEquals($apiKey->getExpiresAt(), ApiKeyFactory::rotate($apiKey)->getExpiresAt());
        self::assertNotNull($apiKey->getUpdatedAt());
    }
}
