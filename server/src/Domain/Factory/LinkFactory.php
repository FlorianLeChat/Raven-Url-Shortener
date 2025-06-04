<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Infrastructure\Security\TrustedDomains;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Fabrique pour les liens raccourcis.
 */
final class LinkFactory
{
	/**
	 * Récupération (si possible) d'une date d'expiration.
	 */
	private static function parseExpiration(?string $expiration = null): ?DateTimeImmutable
	{
		return !empty($expiration) ? new DateTimeImmutable($expiration) : null;
	}

	/**
	 * Création d'un lien raccourci.
	 */
	public static function create(string $url, string $slug, ?string $expiration = null): Link
	{
		$link = new Link();
		$link->setUrl($url = trim($url));
		$link->setSlug(trim($slug));
		$link->setTrusted(TrustedDomains::isTrusted($url));
		$link->setVisitedAt(new DateTimeImmutable());
		$link->setExpiresAt(self::parseExpiration($expiration));

		return $link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	public static function update(Link $link, string $url, string $slug, ?string $expiration = null): Link
	{
		$link->setUrl($url = trim($url));
		$link->setSlug(trim($slug));
		$link->setTrusted(TrustedDomains::isTrusted($url));
		$link->setUpdatedAt(new DateTimeImmutable());
		$link->setExpiresAt(self::parseExpiration($expiration));

		return $link;
	}

	/**
	 * Mise à jour partielle d'un lien raccourci.
	 */
	public static function patch(Link $link, string $field, string $value): Link
	{
		$value = trim($value);

		switch ($field)
		{
			case 'url':
				$link->setUrl($value);
				$link->setTrusted(TrustedDomains::isTrusted($value));
				break;

			case 'slug':
				$link->setSlug($value);
				break;

			case 'expiresAt':
				$value = self::parseExpiration($value);
				break;

			default:
				// C'est bancal mais cela ne devrait pas se produire.
				throw new BadRequestHttpException();
		}

		$link->setUpdatedAt(new DateTimeImmutable());

		return $link;
	}
}