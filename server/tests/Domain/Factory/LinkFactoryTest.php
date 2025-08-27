<?php

namespace App\Tests\Domain\Factory;

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
		$link = LinkFactory::create([
			'url' => 'https://example.com',
			'slug' => 'example',
			'password' => 'password',
			'expiration' => '2023-12-31',
			'custom-domain' => 'my-domain.com'
		]);

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// Identifiant unique.
		$this->assertInstanceOf('Symfony\Component\Uid\Uuid', $link->getId());

		// URL.
		$this->assertEquals('https://example.com', $link->getUrl());

		// Slug.
		$this->assertEquals('example', $link->getSlug());

		// Domaine personnalisé.
		$this->assertEquals('my-domain.com', $link->getCustomDomain());

		// Mot de passe.
		$this->assertStringStartsWith('$2y$', $link->getPassword());

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
			'expiration' => null,
			'custom-domain' => 'my-super-domain.com'
		]);

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// URL.
		$this->assertEquals('https://updated.com', $link->getUrl());

		// Slug.
		$this->assertEquals('updated', $link->getSlug());

		// Domaine personnalisé.
		$this->assertEquals('my-super-domain.com', $link->getCustomDomain());

		// Mot de passe.
		$this->assertStringStartsWith('$2y$', $link->getPassword());

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
		$link = LinkFactory::create([
			'url' => 'https://not-my-example.com',
			'slug' => 'example',
			'password' => 'password',
			'expiration' => '2023-12-30',
			'custom-domain' => 'not-my-domain.com'
		]);

		$link = LinkFactory::patch($link, 'url', 'https://not-updated.com');

		$this->assertInstanceOf('App\Domain\Entity\Link', $link);

		// URL.
		$this->assertEquals('https://not-updated.com', $link->getUrl());

		// Slug.
		$this->assertEquals('example', $link->getSlug());

		// Domaine personnalisé.
		$this->assertEquals('not-my-domain.com', $link->getCustomDomain());

		// Mot de passe.
		$this->assertStringStartsWith('$2y$', $link->getPassword());

		// État d'activation.
		$this->assertEquals(true, $link->isEnabled());

		// Dates de création, de dernière mise à jour et d'expiration.
		$this->assertNotNull($link->getCreatedAt());
		$this->assertNotNull($link->getUpdatedAt());
		$this->assertEquals('2023-12-30', $link->getExpiresAt()->format('Y-m-d'));
	}
}