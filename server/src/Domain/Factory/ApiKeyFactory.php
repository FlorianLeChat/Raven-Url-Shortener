<?php

namespace App\Domain\Factory;

use DateTime;
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
		$currentDate = new DateTime();

		$apiKey = new ApiKey();
		$apiKey->setLink($link);
		$apiKey->setCreatedAt($currentDate);
		$apiKey->setUpdatedAt($currentDate);

		return $apiKey;
	}
}