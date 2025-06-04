<?php

namespace App\Tests\Infrastructure\Security;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Security\TrustedDomains;

/**
 * Test de la classe utilitaire pour la gestion des domaines de confiance.
 */
final class TrustedDomainsTest extends TestCase
{
	/**
	 * Liste de domaines de confiance pour les tests.
	 */
	const TRUSTED_MAIN_DOMAINS = [
		'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
		'https://www.google.com/search?q=example',
		'https://www.facebook.com/user',
		'https://www.twitter.com/user/status/1234567890',
		'https://www.instagram.com/p/1234567890/',
	];

	/**
	 * Liste de sous-domaines de confiance pour les tests.
	 */
	const TRUSTED_SUB_DOMAINS = [
		'https://subdomain.youtube.com/watch?v=dQw4w9WgXcQ',
		'https://subdomain.google.com/search?q=example',
		'https://subdomain.facebook.com/user',
		'https://subdomain.twitter.com/user/status/1234567890',
		'https://subdomain.instagram.com/p/1234567890/',
	];

	/**
	 * Liste de domaines de confiance avec port pour les tests.
	 */
	const TRUSTED_PORT_DOMAINS = [
		'https://www.youtube.com:443/watch?v=dQw4w9WgXcQ',
		'https://www.google.com:443/search?q=example',
		'https://www.facebook.com:443/user',
		'https://www.twitter.com:443/user/status/1234567890',
		'https://www.instagram.com:443/p/1234567890/',
	];

	/**
	 * Liste de domaines non fiables pour les tests.
	 */
	const UNTRUSTED_DOMAINS = [
		'https://www.untrusted-youtube.com/watch?v=dQw4w9WgXcQ',
		'https://www.untrusted-google.com/search?q=example',
		'https://www.untrusted-facebook.com/user',
		'https://www.untrusted-twitter.com/user/status/1234567890',
		'https://www.untrusted-instagram.com/p/1234567890/'
	];

	/**
	 * Test des domaines de confiance principaux.
	 */
	public function testTrustedMainDomains(): void
	{
		foreach (self::TRUSTED_MAIN_DOMAINS as $url)
		{
			$this->assertTrue(TrustedDomains::isTrusted($url), "Le domaine principal « $url » devrait être de confiance.");
		}
	}

	/**
	 * Test des sous-domaines de confiance.
	 */
	public function testTrustedSubDomains(): void
	{
		foreach (self::TRUSTED_SUB_DOMAINS as $url)
		{
			$this->assertTrue(TrustedDomains::isTrusted($url), "Le sous-domaine « $url » devrait être de confiance.");
		}
	}

	/**
	 * Test des domaines de confiance avec port.
	 */
	public function testTrustedPortDomains(): void
	{
		foreach (self::TRUSTED_PORT_DOMAINS as $url)
		{
			$this->assertTrue(TrustedDomains::isTrusted($url), "Le domaine avec port « $url » devrait être de confiance.");
		}
	}

	/**
	 * Test des domaines non fiables.
	 */
	public function testUntrustedDomains(): void
	{
		foreach (self::UNTRUSTED_DOMAINS as $url)
		{
			$this->assertFalse(TrustedDomains::isTrusted($url), "Le domaine non fiable « $url » ne devrait pas être de confiance.");
		}
	}
}