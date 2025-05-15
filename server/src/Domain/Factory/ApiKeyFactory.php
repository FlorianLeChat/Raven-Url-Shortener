<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;

/**
 * Fabrique pour les clés API.
 */
final class ApiKeyFactory
{
	/**
	 * Création d'une clé API.
	 */
	public static function create(Link $link): ApiKey
	{
		$apiKey = new ApiKey();
		$apiKey->setLink($link);
		$apiKey->setCreatedAt(new DateTimeImmutable());

		return $apiKey;
	}
}