<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Infrastructure\Security\TrustedDomains;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class LinkFactory
{
    private const HASH_COST = 15;

    private const HASH_ALGORITHM = PASSWORD_BCRYPT;

    private static function parseExpiration(string $expiration): DateTimeImmutable
    {
        return new DateTimeImmutable($expiration);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::HASH_ALGORITHM, ['cost' => self::HASH_COST]);
    }

    public static function verifyPassword(string $hashedPassword, string $plainPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * @param array{
     *  url: string,
     *  slug: string,
     *  password?: string|null,
     *  expiration?: string|null,
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

        return $link;
    }

    /**
     * @param array{
     *  url: string,
     *  slug: string,
     *  password?: string|null,
     *  expiration?: string|null,
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

        return $link;
    }

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
