<?php

namespace App\Infrastructure\Security;

use App\Domain\Entity\AccessKey;

/**
 * What the application cache keeps of an active access key (see AccessKeyCache): never its value nor its hash.
 */
final readonly class CachedAccessKey
{
    /**
     * @param list<string> $capabilities Values of AccessKeyCapability.
     * @param list<string> $allowedOrigins Browser origins of the key.
     * @param int|null $expiresAt Unix timestamp of the expiration date, null when the key never expires.
     */
    public function __construct(
        public string $id,
        public array $capabilities,
        public array $allowedOrigins,
        public ?int $expiresAt
    ) {
    }

    /**
     * Keeps what the requests need of the key.
     */
    public static function fromAccessKey(AccessKey $accessKey): self
    {
        return new self(
            (string) $accessKey->getId(),
            $accessKey->getCapabilities(),
            $accessKey->getAllowedOrigins(),
            $accessKey->getExpiresAt()?->getTimestamp()
        );
    }

    /**
     * Tells whether the key has expired since it was cached.
     */
    public function isExpired(): bool
    {
        return $this->expiresAt !== null && $this->expiresAt <= time();
    }
}
