<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
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
		$link->setUrl(trim($url));
		$link->setSlug(trim($slug));
		$link->setVisitedAt(new DateTimeImmutable());
		$link->setExpiresAt(self::parseExpiration($expiration));

		return $link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	public static function update(Link $link, string $url, string $slug, ?string $expiration = null): Link
	{
		$link->setUrl(trim($url));
		$link->setSlug(trim($slug));
		$link->setUpdatedAt(new DateTimeImmutable());
		$link->setExpiresAt(self::parseExpiration($expiration));

		return $link;
	}

	/**
	 * Mise à jour partielle d'un lien raccourci.
	 */
	public static function patch(Link $link, string $field, string $value): Link
	{
		match ($field)
		{
			'url' => $link->setUrl(trim($value)),
			'slug' => $link->setSlug(trim($value)),
			'visitedAt' => $link->setVisitedAt(new DateTimeImmutable()),
			'expiresAt' => $link->setExpiresAt(self::parseExpiration($value)),
			default => throw new BadRequestHttpException() // C'est bancal mais cela ne devrait pas se produire.
		};

		$link->setUpdatedAt(new DateTimeImmutable());

		return $link;
	}
}