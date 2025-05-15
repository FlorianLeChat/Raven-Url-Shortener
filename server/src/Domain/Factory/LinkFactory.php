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
		$url = trim($url);
		$slug = trim($slug);
		$expiration = self::parseExpiration($expiration);
		$currentDate = new DateTimeImmutable();

		$link = new Link();
		$link->setUrl($url);
		$link->setSlug($slug);
		$link->setExpiration($expiration);
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		return $link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	public static function update(Link $link, string $url, string $slug, ?string $expiration = null): Link
	{
		$url = trim($url);
		$slug = trim($slug);
		$expiration = self::parseExpiration($expiration);
		$currentDate = new DateTimeImmutable();

		$link->setUrl($url);
		$link->setSlug($slug);
		$link->setExpiration($expiration);
		$link->setUpdatedAt($currentDate);

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
			'expiration' => $link->setExpiration(self::parseExpiration($value)),
			default => throw new BadRequestHttpException() // C'est bancal mais cela ne devrait pas se produire.
		};

		return $link;
	}
}