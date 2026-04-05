<?php

namespace App\Tests\Domain\Factory;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ApiKeyFactory;

final class ApiKeyFactoryTest extends TestCase
{
    public function testCreateApiKey(): void
    {
        $apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));

        $this->assertEquals(44, strlen($apiKey->getKey()));
        $this->assertNotNull($apiKey->getCreatedAt());
        $this->assertNull($apiKey->getUpdatedAt());
        $this->assertLessThan((new DateTimeImmutable())->modify('+3 months'), $apiKey->getExpiresAt());
    }

    public function testRotateApiKey(): void
    {
        $apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));

        $this->assertNotEquals($apiKey->getKey(), ApiKeyFactory::rotate($apiKey)->getKey());
        $this->assertNotEquals($apiKey->getExpiresAt(), ApiKeyFactory::rotate($apiKey)->getExpiresAt());
        $this->assertNotNull($apiKey->getUpdatedAt());
    }
}
