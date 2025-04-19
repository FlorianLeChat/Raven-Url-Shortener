<?php

namespace App\Domain\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité pour les liens raccourcis.
 */
#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
	#[ORM\Id]
	#[ORM\Column(type: UuidType::NAME, unique: true)]
	private ?Uuid $id = null;

	#[ORM\Column(type: Types::TEXT)]
	#[Assert\Url(requireTld: true)]
	#[Assert\NotNull]
	#[Assert\NotBlank]
	private ?string $url = null;

	#[ORM\Column(type: Types::STRING, length: 50)]
	#[Assert\Regex(pattern: '/^[a-zA-Z0-9-]+$/')]
	#[Assert\Length(min: 1, max: 50)]
	private ?string $slug = null;

	#[ORM\Column(type: Types::BOOLEAN)]
	private ?bool $enabled = true;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	#[Assert\Range(min: 'tomorrow', max: '+1 year')]
	private ?DateTimeInterface $expiration = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?DateTimeInterface $visitedAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?DateTimeInterface $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?DateTimeInterface $updatedAt = null;

	/** @var Collection<int, Report> */
	#[ORM\OneToMany(mappedBy: "link", targetEntity: Report::class, orphanRemoval: true, cascade: ["persist", "remove"])]
	private Collection $reports;

	/**
	 * Création des certaines propriétés de l'entité.
	 * @see https://github.com/symfony/symfony/discussions/53331
	 */
	public function __construct()
	{
		$this->id = Uuid::v7();
	}

	/**
	 * Définition ou récupération de l'identifiant du lien.
	 */
	public function getId(): ?Uuid
	{
		return $this->id;
	}

	public function setId(?Uuid $id): static
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Définition ou récupération de l'URL du lien.
	 */
	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function setUrl(?string $url): static
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * Définition ou récupération du slug du lien.
	 */
	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(?string $slug): static
	{
		$this->slug = $slug;

		return $this;
	}

	/**
	 * Définition ou récupération de l'état d'activation d'un lien.
	 */
	public function getEnabled(): ?bool
	{
		return $this->enabled;
	}

	public function setEnabled(?bool $enabled): static
	{
		$this->enabled = $enabled;

		return $this;
	}

	/**
	 * Définition ou récupération de la date d'expiration du lien.
	 */
	public function getExpiration(): ?DateTimeInterface
	{
		return $this->expiration;
	}

	public function setExpiration(?DateTimeInterface $expiration): static
	{
		$this->expiration = $expiration;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de dernière visite du lien.
	 */
	public function getVisitedAt(): ?DateTimeInterface
	{
		return $this->visitedAt;
	}

	public function setVisitedAt(?DateTimeInterface $visitedAt): static
	{
		$this->visitedAt = $visitedAt;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de création du lien.
	 */
	public function getCreatedAt(): ?DateTimeInterface
	{
		return $this->createdAt;
	}

	public function setCreatedAt(?DateTimeInterface $createdAt): static
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de dernière modification du lien.
	 */
	public function getUpdatedAt(): ?DateTimeInterface
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(DateTimeInterface $updatedAt): static
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * Récupération des signalements du lien raccourci.
	 * @return Collection<int, Report>
	 */
	public function getReports()
	{
		return $this->reports;
	}

	/**
	 * Conversion de l'entité en tableau.
	 * @return array<string, Link>
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'url' => $this->getUrl(),
			'slug' => $this->getSlug(),
			'enabled' => $this->getEnabled(),
			'expiration' => $this->getExpiration(),
			'visitedAt' => $this->getVisitedAt(),
			'createdAt' => $this->getCreatedAt(),
			'updatedAt' => $this->getUpdatedAt()
		];
	}
}
