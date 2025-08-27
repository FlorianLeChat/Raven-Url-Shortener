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
	 * Coût de hachage par défaut pour les mots de passe.
	 */
	private const HASH_COST = 15;

	/**
	 * Algorithme de hachage par défaut pour les mots de passe.
	 */
	private const HASH_ALGORITHM = PASSWORD_BCRYPT;

	/**
	 * Récupération (si possible) d'une date d'expiration.
	 */
	private static function parseExpiration(string $expiration): DateTimeImmutable
	{
		return new DateTimeImmutable($expiration);
	}

	/**
	 * Hachage un mot de passe.
	 */
	public static function hashPassword(string $password): string
	{
		return password_hash($password, self::HASH_ALGORITHM, ['cost' => self::HASH_COST]);
	}

	/**
	 * Vérification d'un mot de passe haché.
	 */
	public static function verifyPassword(string $hashedPassword, string $plainPassword): bool
	{
		return password_verify($plainPassword, $hashedPassword);
	}

	/**
	 * Création d'un lien raccourci.
	 * @param array{
	 *  url: string,
	 *  slug: string,
	 *  password?: string|null,
	 *  expiration?: string|null,
	 *  custom-domain?: string|null
	 * } $options
	 */
	public static function create(array $options): Link
	{
		$url = $options['url'];

		$password = $options['password'] ?? null;
		$password = !empty($password) ? self::hashPassword($password) : null;

		$expiration = $options['expiration'] ?? null;
		$expiration = !empty($expiration) ? self::parseExpiration($expiration) : null;

		$link = new Link();
		$link->setUrl($url);
		$link->setSlug($options['slug']);
		$link->setTrusted(TrustedDomains::isTrusted($url));
		$link->setPassword($password);
		$link->setExpiresAt($expiration);
		$link->setCustomDomain($options['custom-domain'] ?? null);

		return $link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 * @param array{
	 *  url: string,
	 *  slug: string,
	 *  password?: string|null,
	 *  expiration?: string|null,
	 *  custom-domain?: string|null
	 * } $options
	 */
	public static function update(Link $link, array $options): Link
	{
		$url = $options['url'];

		$password = $options['password'] ?? null;
		$password = !empty($password) ? self::hashPassword($password) : null;

		$expiration = $options['expiration'] ?? null;
		$expiration = !empty($expiration) ? self::parseExpiration($expiration) : null;

		$link->setUrl($url);
		$link->setSlug($options['slug']);
		$link->setTrusted(TrustedDomains::isTrusted($url));
		$link->setPassword($password);
		$link->setUpdatedAt(new DateTimeImmutable());
		$link->setExpiresAt($expiration);
		$link->setCustomDomain($options['custom-domain'] ?? null);

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

			case 'password':
				$link->setPassword(self::hashPassword($value));
				break;

			case 'expiresAt':
				$link->setExpiresAt(self::parseExpiration($value));
				break;

			case 'custom-domain':
				$link->setCustomDomain($value);
				break;

			default:
				// C'est bancal mais cela ne devrait pas se produire.
				throw new BadRequestHttpException();
		}

		$link->setUpdatedAt(new DateTimeImmutable());

		return $link;
	}
}