<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\ReportRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité pour les signalements de liens raccourcis.
 */
#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
	#[ORM\Id]
	#[ORM\Column(type: UuidType::NAME, unique: true)]
	private ?Uuid $id = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\Type(type: Link::class)]
	private ?Link $link = null;

	#[ORM\Column(type: Types::STRING, length: 500)]
	#[Assert\Length(min: 10, max: 500)]
	private ?string $reason = null;

	#[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
	#[Assert\Email]
	#[Assert\Length(min: 10, max: 100)]
	#[Assert\NoSuspiciousCharacters]
	private ?string $email = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private ?DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
	private ?DateTimeImmutable $updatedAt = null;

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

	/**
	 * Définition ou récupération d'un lien raccourci.
	 */
	public function getLink(): ?Link
	{
		return $this->link;
	}

	public function setLink(?Link $link): static
	{
		$this->link = $link;

		return $this;
	}

	/**
	 * Définition ou récupération de la raison du signalement.
	 */
	public function getReason(): ?string
	{
		return $this->reason;
	}

	public function setReason(?string $reason): static
	{
		$this->reason = $reason;

		return $this;
	}

	/**
	 * Définition ou récupération de l'adresse électronique
	 *  de l'auteur du signalement.
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(?string $email): static
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de création du signalement.
	 */
	public function getCreatedAt(): ?DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function setCreatedAt(?DateTimeImmutable $createdAt): static
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de dernière modification du signalement.
	 */
	public function getUpdatedAt(): ?DateTimeImmutable
	{
		return $this->updatedAt;
	}

	public function setUpdatedAt(DateTimeImmutable $updatedAt): static
	{
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * Conversion de l'entité en tableau.
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'link' => $this->getLink()?->toArray(),
			'reason' => $this->getReason(),
			'email' => $this->getEmail(),
			'createdAt' => $this->getCreatedAt(),
			'updatedAt' => $this->getUpdatedAt()
		];
	}
}
