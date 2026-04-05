<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\ApiKey;

final class ApiKeyFactory
{
    private const KEY_LENGTH = 32;

    private static function generateKey(): string
    {
        return base64_encode(random_bytes(self::KEY_LENGTH));
    }

    public static function create(Link $link): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setKey(self::generateKey());
        $apiKey->setLink($link);
        $apiKey->setExpiresAt((new DateTimeImmutable())->modify('+3 months'));

        return $apiKey;
    }

    public static function rotate(ApiKey $apiKey): ApiKey
    {
        $currentDate = new DateTimeImmutable();

        $apiKey->setKey(self::generateKey());
        $apiKey->setUpdatedAt($currentDate);
        $apiKey->setExpiresAt($currentDate->modify('+3 months'));

        return $apiKey;
    }
}
