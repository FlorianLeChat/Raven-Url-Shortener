<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use Random\RandomException;
use App\Domain\Entity\ApiKey;

/**
 * Creates and rotates the API keys of the links.
 */
final class ApiKeyFactory
{
    private const KEY_LENGTH = 32;

    /**
     * Generates a random key of 32 bytes, encoded in base64 (44 characters).
     * @throws RandomException
     */
    private static function generateKey(): string
    {
        return base64_encode(random_bytes(self::KEY_LENGTH));
    }

    /**
     * Creates a key for the link, valid for three months.
     * @throws RandomException
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
     * Replaces the value of the key and extends its validity by three months from now.
     * @throws RandomException
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
