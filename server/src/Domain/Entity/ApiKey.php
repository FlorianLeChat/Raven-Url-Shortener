<?php

namespace App\Domain\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use App\Infrastructure\Repository\ApiKeyRepository;

/**
 * Entité pour les clés API de gestion des liens raccourcis.
 */
#[ORM\Entity(repositoryClass: ApiKeyRepository::class)]
class ApiKey
{
	#[ORM\Id]
	#[ORM\Column(type: UuidType::NAME, unique: true)]
	private ?Uuid $id = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	private ?Link $link = null;

	#[ORM\Column(type: Types::STRING, length: 64)]
	private ?string $apiKey = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE)]
	private ?DateTimeInterface $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
	private ?DateTimeInterface $updatedAt = null;

	/**
	 * Création des certaines propriétés de l'entité.
	 * @see https://github.com/symfony/symfony/discussions/53331
	 */
	public function __construct()
	{
		$this->id = Uuid::v7();
		$this->apiKey = Uuid::v4()->toRfc4122();
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
	 * Définition ou récupération de la clé API.
	 */
	public function getApiKey(): ?string
	{
		return $this->apiKey;
	}

	public function setApiKey(?string $apiKey): static
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de création de la clé API.
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
	 * Définition ou récupération de la date de dernière modification de la clé API.
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
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->getId(),
			'link' => $this->getLink()?->toArray(),
			'apiKey' => $this->getApiKey(),
			'createdAt' => $this->getCreatedAt(),
			'updatedAt' => $this->getUpdatedAt()
		];
	}
}
