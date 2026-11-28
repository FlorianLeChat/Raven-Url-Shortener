<?php

namespace App\Infrastructure\Security;

use Random\RandomException;

/**
 * Generates and hashes the random keys the API hands out: the API keys of the links and the access keys.
 * Only their hash is stored, so a leaked database discloses no usable key.
 */
final class SecretKey
{
    private const KEY_LENGTH = 32;

    private const HASH_ALGORITHM = 'sha256';

    /**
     * Shape of a generated key: 32 bytes encoded in base64, so 43 characters and one padding `=`.
     */
    private const KEY_PATTERN = '/^[A-Za-z0-9+\/]{43}=$/';

    /**
     * Generates a random key of 32 bytes, encoded in base64 (44 characters).
     * @throws RandomException
     */
    public static function generate(): string
    {
        return base64_encode(random_bytes(self::KEY_LENGTH));
    }

    /**
     * Tells whether the value has the shape of a generated key, so a malformed one is refused without any lookup.
     */
    public static function isWellFormed(string $key): bool
    {
        return preg_match(self::KEY_PATTERN, $key) === 1;
    }

    /**
     * Hashes a key with SHA-256, in hexadecimal (64 characters).
     * A fast hash is enough: the key holds 32 random bytes, out of reach of a brute force attack.
     */
    public static function hash(string $key): string
    {
        return hash(self::HASH_ALGORITHM, $key);
    }
}
