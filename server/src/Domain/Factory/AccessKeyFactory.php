<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use Random\RandomException;
use App\Domain\Entity\AccessKey;
use App\Infrastructure\Security\SecretKey;

/**
 * Creates and revokes the access keys.
 */
final class AccessKeyFactory
{
    /**
     * Creates a key with a random value. The clear value stays on the returned key for the creation response only.
     *
     * The origins are written as browsers send them: lowercase, without trailing slash.
     *
     * @param list<string> $capabilities Values of AccessKeyCapability, checked by the constraints of AccessKey.
     * @param list<string> $allowedOrigins Browser origins of the key, checked by the constraints of AccessKey.
     * @param DateTimeImmutable|null $expiresAt Null, the key never expires.
     * @throws RandomException
     */
    public static function create(
        string $name,
        array $capabilities,
        array $allowedOrigins,
        ?DateTimeImmutable $expiresAt
    ): AccessKey {
        $plainKey = SecretKey::generate();
        $origins = array_map(static fn (string $origin) => strtolower(rtrim(trim($origin), '/')), $allowedOrigins);

        $accessKey = new AccessKey();
        $accessKey->setName(trim($name));
        $accessKey->setKey(SecretKey::hash($plainKey));
        $accessKey->setPlainKey($plainKey);
        $accessKey->setCapabilities($capabilities);
        $accessKey->setAllowedOrigins($origins);
        $accessKey->setExpiresAt($expiresAt);

        return $accessKey;
    }

    /**
     * Stamps the revocation date: from then on, the key is refused.
     */
    public static function revoke(AccessKey $accessKey): AccessKey
    {
        $accessKey->setRevokedAt(new DateTimeImmutable());

        return $accessKey;
    }
}
