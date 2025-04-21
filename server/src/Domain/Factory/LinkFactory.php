<?php

namespace App\Domain\Factory;

use DateTime;
use App\Domain\Entity\Link;

/**
 * Fabrique pour les liens raccourcis.
 */
final class LinkFactory
{
	/**
	 * Récupération (si possible) d'une date d'expiration.
	 */
	private static function parseExpiration(?string $expiration = null): ?DateTime
	{
		return !empty($expiration) ? new DateTime($expiration) : null;
	}

	/**
	 * Création d'un lien raccourci.
	 */
	public static function create(string $url, string $slug, ?string $expiration = null): Link
	{
		$url = trim($url);
		$slug = trim($slug);
		$expiration = self::parseExpiration($expiration);
		$currentDate = new DateTime();

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
		$currentDate = new DateTime();

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
			'visitedAt' => $link->setVisitedAt(new DateTime()),
			'expiration' => $link->setExpiration(self::parseExpiration($value)),
			default => null
		};

		return $link;
	}
}