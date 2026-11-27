<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\ApiKeyRepository;

/**
 * Key granting access to the management API of a single link.
 * Only its SHA-256 hash is stored: the clear value is known to the request that created it, then lost.
 * It expires three months after its creation and is rotated by the `app:api-keys-rotation` command.
 */
#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\OneToOne(inversedBy: 'apiKey')]
    #[ORM\JoinColumn(nullable: false)]
    private Link $link;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private string $key;

    /**
     * Clear value of the key, set only by the factory that generated it and never persisted:
     *  a key read from the database has none.
     */
    private ?string $plainKey = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $expiresAt;

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

    public function getLink(): Link
    {
        return $this->link;
    }

    public function setLink(Link $link): static
    {
        $this->link = $link;

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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Serializes the key with the link it belongs to.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'link' => $this->getLink()->toArray(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'expiresAt' => $this->getExpiresAt()
        ];
    }
}
