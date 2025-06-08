<?php

namespace App\Tests\Domain\Factory;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use App\Domain\Factory\LinkFactory;

/**
 * Test de la fabrique de liens raccourcis.
 */
final class LinkFactoryTest extends TestCase
{
	/**
	 * Test de la création d'un lien raccourci.
	 */
	public function testCreateLink(): void
	{
		$time = new DateTimeImmutable();
		$link = LinkFactory::create('https://example.com', 'example', '2023-12-31');

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// Identifiant unique.
		$this->assertInstanceOf('Symfony\Component\Uid\Uuid', $link->getId());

		// URL.
		$this->assertEquals('https://example.com', $link->getUrl());

		// Slug.
		$this->assertEquals('example', $link->getSlug());

		// État d'activation.
		$this->assertEquals(true, $link->isEnabled());

		// Dates de création, de dernière mise à jour et d'expiration.
		$this->assertNotNull($link->getCreatedAt());
		$this->assertNull($link->getUpdatedAt());
		$this->assertEquals('2023-12-31', $link->getExpiresAt()->format('Y-m-d'));
	}

	/**
	 * Test de la mise à jour complète d'un lien raccourci.
	 */
	public function testUpdateLink(): void
	{
		$time = new DateTimeImmutable();
		$link = LinkFactory::create('https://my-example.com', 'example', '2023-12-20');
		$link = LinkFactory::update($link, 'https://updated.com', 'updated');

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// URL.
		$this->assertEquals('https://updated.com', $link->getUrl());

		// Slug.
		$this->assertEquals('updated', $link->getSlug());

		// État d'activation.
		$this->assertEquals(true, $link->isEnabled());

		// Dates de création, de dernière mise à jour et d'expiration.
		$this->assertNotNull($link->getCreatedAt());
		$this->assertNotNull($link->getUpdatedAt());
		$this->assertNull($link->getExpiresAt());
	}

	/**
	 * Test de la mise à jour partielle d'un lien raccourci.
	 */
	public function testPatchLink(): void
	{
		$time = new DateTimeImmutable();
		$link = LinkFactory::create('https://not-my-example.com', 'example', '2023-12-30');
		$link = LinkFactory::patch($link, 'url', 'https://not-updated.com');

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// URL.
		$this->assertEquals('https://not-updated.com', $link->getUrl());

		// Slug.
		$this->assertEquals('example', $link->getSlug());

		// État d'activation.
		$this->assertEquals(true, $link->isEnabled());

		// Dates de création, de dernière mise à jour et d'expiration.
		$this->assertNotNull($link->getCreatedAt());
		$this->assertNotNull($link->getUpdatedAt());
		$this->assertEquals('2023-12-30', $link->getExpiresAt()->format('Y-m-d'));
	}
}