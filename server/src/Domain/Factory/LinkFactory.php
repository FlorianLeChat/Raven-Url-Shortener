<?php

namespace App\Domain\Factory;

use Exception;
use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Infrastructure\Security\TrustedDomains;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Creates and updates links from the values received by the API.
 * Passwords are hashed and the trust flag is recomputed from the URL each time it changes.
 */
final class LinkFactory
{
    private const HASH_COST = 15;

    private const HASH_ALGORITHM = PASSWORD_BCRYPT;

    /**
     * Parses a date in any format understood by DateTimeImmutable, in the default time zone.
     * @throws Exception
     */
    private static function parseExpiration(string $expiration): DateTimeImmutable
    {
        return new DateTimeImmutable($expiration);
    }

    /**
     * Hashes a link password with bcrypt.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::HASH_ALGORITHM, ['cost' => self::HASH_COST]);
    }

    /**
     * Tells whether the plain password matches the stored hash.
     */
    public static function verifyPassword(string $hashedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Creates a link. An empty password or expiration date leaves the option off.
     *
     * @param array{
     *  url: string,
     *  slug: string,
     *  password?: string|null,
     *  expiration?: string|null,
     * } $options
     * @throws Exception
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

        return $link;
    }

    /**
     * Replaces every value of the link. An empty password or expiration date removes the option.
     * A password left out keeps the current hash: hashing it again would lock the link.
     *
     * @param array{
     *  url: string,
     *  slug: string,
     *  password?: string|null,
     *  expiration?: string|null,
     * } $options
     * @throws Exception
     */
    public static function update(Link $link, array $options): Link
    {
        $url = $options['url'];

        $expiration = $options['expiration'] ?? null;
        $expiration = !empty($expiration) ? self::parseExpiration($expiration) : null;

        if (array_key_exists('password', $options)) {
            $password = $options['password'];
            $link->setPassword(!empty($password) ? self::hashPassword($password) : null);
        }

        $link->setUrl($url);
        $link->setSlug($options['slug']);
        $link->setTrusted(TrustedDomains::isTrusted($url));
        $link->setUpdatedAt(new DateTimeImmutable());
        $link->setExpiresAt($expiration);

        return $link;
    }

    /**
     * Updates a single field of the link: `url`, `slug`, `password` or `expiresAt`.
     * @throws BadRequestHttpException When the field cannot be updated this way.
     * @throws Exception
     */
    public static function patch(Link $link, string $field, string $value): Link
    {
        $value = trim($value);

        switch ($field) {
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

            default:
                throw new BadRequestHttpException();
        }

        $link->setUpdatedAt(new DateTimeImmutable());

        return $link;
    }
}
