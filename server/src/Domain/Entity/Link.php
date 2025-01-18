<?php

namespace App\Domain\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entité pour les liens raccourcis.
 */
#[ORM\Entity(repositoryClass: LinkRepository::class)]
final class Link
{
	#[ORM\Id]
	#[ORM\Column(type: UuidType::NAME, unique: true)]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
	private ?Uuid $id = null;

	#[ORM\Column(type: Types::TEXT)]
	#[Assert\Url]
	#[Assert\NotNull]
	#[Assert\NotBlank]
	private ?string $url = null;

	#[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
	#[Assert\Length(min: 1, max: 50)]
	private ?string $slug = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	#[Assert\Range(min: "-1 day", max: "+1 year")]
	private ?DateTimeInterface $expiration = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?DateTimeInterface $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?DateTimeInterface $updatedAt = null;

	/**
	 * Définition ou récupération de l'identifiant du lien.
	 */
	public function getId(): Uuid
	{
		return $this->id;
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
	 * Conversion de l'entité en tableau.
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}
}
