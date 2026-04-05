<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Doctrine\Common\Collections\ArrayCollection;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Url(requireTld: true)]
    #[Assert\NotBlank(normalizer: 'trim')]
    private string $url;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    #[Assert\Regex(pattern: '/^[a-zA-Z0-9-]+$/')]
    #[Assert\Length(min: 1, max: 50)]
    #[Assert\NotBlank(normalizer: 'trim')]
    private string $slug;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $password = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $trusted = false;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $visitedAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    #[Assert\Range(min: 'tomorrow', max: '+1 year')]
    private ?DateTimeImmutable $expiresAt = null;

    /** @var Collection<int, Report> */
    #[ORM\OneToMany(targetEntity: Report::class, mappedBy: "link", cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $reports;

    #[ORM\OneToOne(targetEntity: ApiKey::class, mappedBy: "link", cascade: ["persist", "remove"], orphanRemoval: true)]
    private ?ApiKey $apiKey = null;

    public function __construct()
    {
        $this->id = Uuid::v7();
        $this->reports = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isTrusted(): bool
    {
        return $this->trusted;
    }

    public function setTrusted(bool $trusted): static
    {
        $this->trusted = $trusted;

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

    public function getVisitedAt(): ?DateTimeImmutable
    {
        return $this->visitedAt;
    }

    public function setVisitedAt(?DateTimeImmutable $visitedAt): static
    {
        $this->visitedAt = $visitedAt;

        return $this;
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

    /**
     * @return Collection<int, Report>
     */
    public function getReports()
    {
        return $this->reports;
    }

    public function getApiKey(): ?ApiKey
    {
        return $this->apiKey;
    }

    public function setApiKey(ApiKey $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->getVisitedAt() === null) {
            $apiKey = $this->getApiKey()?->getKey();
        }

        return [
            'id' => $this->getId(),
            'url' => $this->getUrl(),
            'slug' => $this->getSlug(),
            'apiKey' => $apiKey ?? '**hidden**',
            'enabled' => $this->isEnabled(),
            'trusted' => $this->isTrusted(),
            'reported' => count($this->getReports()) > 0,
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
            'visitedAt' => $this->getVisitedAt(),
            'expiresAt' => $this->getExpiresAt()
        ];
    }
}
