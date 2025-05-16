<?php

namespace App\Tests\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ApiKeyFactory;

/**
 * Test de la fabrique de clés API.
 */
final class ApiKeyFactoryTest extends TestCase
{
	/**
	 * Test de la création d'une clé API.
	 */
	public function testCreateApiKey(): void
	{
		$apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));

		$this->assertInstanceOf('App\Domain\Entity\ApiKey', $apiKey);

		// Identifiant unique.
		$this->assertInstanceOf('Symfony\Component\Uid\Uuid', $apiKey->getId());

		// Lien raccourci associé.
		$this->assertInstanceOf('App\Domain\Entity\Link', $apiKey->getLink());

		// Clé API.
		$this->assertEquals(44, strlen($apiKey->getKey()));

		// Dates de création, de dernière mise à jour et d'expiration.
		$this->assertNotNull($apiKey->getCreatedAt());
		$this->assertNull($apiKey->getUpdatedAt());
		$this->assertLessThan((new \DateTimeImmutable())->modify('+3 months'), $apiKey->getExpiresAt());
	}

	/**
	 * Test de la rotation d'une clé API.
	 */
	public function testRotateApiKey(): void
	{
		$apiKey = ApiKeyFactory::create($this->createMock('App\Domain\Entity\Link'));

		// Clé API.
		$this->assertNotEquals($apiKey->getKey(), ApiKeyFactory::rotate($apiKey)->getKey());

		// Dates de de dernière mise à jour et d'expiration.
		$this->assertNotEquals($apiKey->getExpiresAt(), ApiKeyFactory::rotate($apiKey)->getExpiresAt());
		$this->assertNotNull($apiKey->getUpdatedAt());
	}
}