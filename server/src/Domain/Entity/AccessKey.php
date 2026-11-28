<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Enum\AccessKeyCapability;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\AccessKeyRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Key an administrator hands to a trusted person, sent in the `X-Api-Key` header instead of sharing
 *  `PRIVATE_API_KEY` or `ADMIN_API_KEY`. Each key grants its own capabilities (see AccessKeyCapability).
 * Only its SHA-256 hash is stored: the clear value is known to the request that created it, then lost.
 * A key stops working once it expires or is revoked; a revoked key stays listed to keep track of it.
 */
#[ORM\Entity(repositoryClass: AccessKeyRepository::class)]
class AccessKey
{
    /**
     * Shape of an origin as browsers send it in the `Origin` header: a lowercase scheme and host, an optional port,
     *  no path.
     */
    public const ORIGIN_PATTERN = '/^https?:\/\/[a-z0-9.-]+(:\d{1,5})?$/';

    private const MAX_ALLOWED_ORIGINS = 10;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank(normalizer: 'trim')]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 64, unique: true)]
    private string $key;

    /**
     * Clear value of the key, set only by the factory that generated it and never persisted:
     *  a key read from the database has none.
     */
    private ?string $plainKey = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Count(min: 1)]
    #[Assert\Unique]
    #[Assert\Choice(callback: [AccessKeyCapability::class, 'values'], multiple: true)]
    private array $capabilities = [];

    /**
     * Browser origins allowed to call a private API with the key, read only when it grants `private_api`.
     *
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Count(max: self::MAX_ALLOWED_ORIGINS)]
    #[Assert\Unique]
    #[Assert\All([new Assert\Regex(pattern: self::ORIGIN_PATTERN)])]
    private array $allowedOrigins = [];

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    #[Assert\Range(min: 'tomorrow', max: '+1 year')]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $revokedAt = null;

    /**
     * Generates the identifier and stamps the creation date.
     */
    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    public function getPlainKey(): ?string
    {
        return $this->plainKey;
    }

    public function setPlainKey(?string $plainKey): static
    {
        $this->plainKey = $plainKey;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @param list<string> $capabilities
     */
    public function setCapabilities(array $capabilities): static
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getAllowedOrigins(): array
    {
        return $this->allowedOrigins;
    }

    /**
     * @param list<string> $allowedOrigins
     */
    public function setAllowedOrigins(array $allowedOrigins): static
    {
        $this->allowedOrigins = $allowedOrigins;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function setRevokedAt(?DateTimeImmutable $revokedAt): static
    {
        $this->revokedAt = $revokedAt;

        return $this;
    }

    /**
     * Tells whether the key is still accepted: neither revoked nor expired.
     * A key without expiration date never expires.
     */
    public function isActive(): bool
    {
        $isExpired = $this->expiresAt !== null && $this->expiresAt <= new DateTimeImmutable();

        return $this->revokedAt === null && !$isExpired;
    }

    /**
     * Serializes the key for the administrator endpoints, without its hash.
     * The clear value appears only in the response creating the key, the one request that knows it.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'key' => $this->getPlainKey(),
            'capabilities' => $this->getCapabilities(),
            'allowedOrigins' => $this->getAllowedOrigins(),
            'active' => $this->isActive(),
            'createdAt' => $this->getCreatedAt(),
            'expiresAt' => $this->getExpiresAt(),
            'revokedAt' => $this->getRevokedAt()
        ];
    }
}
