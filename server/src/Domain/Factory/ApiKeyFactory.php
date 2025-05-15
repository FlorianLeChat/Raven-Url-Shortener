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
	 * Longueur de la clé API.
	 */
	private const KEY_LENGTH = 32;

	/**
	 * Génération d'une clé API aléatoire.
	 */
	private static function generateKey(): string
	{
		return base64_encode(openssl_random_pseudo_bytes(self::KEY_LENGTH));
	}

	/**
	 * Création d'une clé API.
	 */
	public static function create(Link $link): ApiKey
	{
		$apiKey = new ApiKey();
		$apiKey->setKey(self::generateKey());
		$apiKey->setLink($link);
		$apiKey->setExpiresAt((new DateTimeImmutable())->modify('+3 months'));

		return $apiKey;
	}

	/**
	 * Rotation d'une clé API.
	 */
	public static function rotate(ApiKey $apiKey): ApiKey
	{
		$currentDate = new DateTimeImmutable();

		$apiKey->setKey(self::generateKey());
		$apiKey->setUpdatedAt($currentDate);
		$apiKey->setExpiresAt($currentDate->modify('+3 months'));

		return $apiKey;
	}
}