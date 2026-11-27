<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use Random\RandomException;
use App\Domain\Entity\ApiKey;

/**
 * Creates, rotates and hashes the API keys of the links.
 */
final class ApiKeyFactory
{
    private const KEY_LENGTH = 32;

    private const HASH_ALGORITHM = 'sha256';

    /**
     * Generates a random key of 32 bytes, encoded in base64 (44 characters).
     * @throws RandomException
     */
    private static function generateKey(): string
    {
        return base64_encode(random_bytes(self::KEY_LENGTH));
    }

    /**
     * Hashes a key with SHA-256, in hexadecimal (64 characters).
     * A fast hash is enough: the key holds 32 random bytes, out of reach of a brute force attack.
     */
    public static function hashKey(string $key): string
    {
        return hash(self::HASH_ALGORITHM, $key);
    }

    /**
     * Creates a key for the link, valid for three months.
     * The clear value stays on the returned key for the creation response only.
     * @throws RandomException
     */
    public static function create(Link $link): ApiKey
    {
        $plainKey = self::generateKey();

        $apiKey = new ApiKey();
        $apiKey->setKey(self::hashKey($plainKey));
        $apiKey->setPlainKey($plainKey);
        $apiKey->setLink($link);
        $apiKey->setExpiresAt((new DateTimeImmutable())->modify('+3 months'));

        return $apiKey;
    }

    /**
     * Replaces the value of the key and extends its validity by three months from now.
     * The new value is never disclosed: once rotated, the link can no longer be managed through the API.
     * @throws RandomException
     */
    public static function rotate(ApiKey $apiKey): ApiKey
    {
        $currentDate = new DateTimeImmutable();

        $apiKey->setKey(self::hashKey(self::generateKey()));
        $apiKey->setUpdatedAt($currentDate);
        $apiKey->setExpiresAt($currentDate->modify('+3 months'));

        return $apiKey;
    }
}
