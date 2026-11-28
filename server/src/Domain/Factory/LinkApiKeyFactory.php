<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use Random\RandomException;
use App\Domain\Entity\LinkApiKey;
use App\Infrastructure\Security\SecretKey;

/**
 * Creates and rotates the API keys of the links.
 */
final class LinkApiKeyFactory
{
    /**
     * Creates a key for the link, valid for three months.
     * The clear value stays on the returned key for the creation response only.
     * @throws RandomException
     */
    public static function create(Link $link): LinkApiKey
    {
        $plainKey = SecretKey::generate();

        $apiKey = new LinkApiKey();
        $apiKey->setKey(SecretKey::hash($plainKey));
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
    public static function rotate(LinkApiKey $apiKey): LinkApiKey
    {
        $currentDate = new DateTimeImmutable();

        $apiKey->setKey(SecretKey::hash(SecretKey::generate()));
        $apiKey->setUpdatedAt($currentDate);
        $apiKey->setExpiresAt($currentDate->modify('+3 months'));

        return $apiKey;
    }
}
