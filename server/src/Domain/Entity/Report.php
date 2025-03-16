<?php

namespace App\Domain\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\ReportRepository;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité pour les signalements de liens raccourcis.
 */
#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
	#[ORM\Id]
	#[ORM\Column(type: UuidType::NAME, unique: true)]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	private ?Uuid $id = null;

	#[ORM\ManyToOne(cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(nullable: false)]
	private ?Link $link = null;

	#[ORM\Column(type: Types::STRING, length: 500)]
	#[Assert\Length(min: 10, max: 500, minMessage: 'too_short_reason', maxMessage: 'too_long_reason')]
	private ?string $reason = null;

	#[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
	#[Assert\Email(message: 'invalid_email')]
	#[Assert\Length(min: 10, max: 100, minMessage: 'too_short_email', maxMessage: 'too_long_email')]
	#[Assert\NoSuspiciousCharacters]
	private ?string $email = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?DateTimeInterface $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?DateTimeInterface $updatedAt = null;

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
	 * Définition ou récupération de la date de dernière modification du signalement.
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
	 * Conversion de l'entité en tableau.
	 * @return array<string, Report>
	 */
	public function toArray(): array
	{
		return array_filter(
			get_object_vars($this),
			fn($value) => $value instanceof Report
		);
	}
}
