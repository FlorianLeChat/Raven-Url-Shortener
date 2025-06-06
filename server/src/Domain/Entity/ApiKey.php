<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use OpenApi\Attributes as OA;
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
	#[OA\Property(title: 'The unique identifier of the API key')]
	private ?Uuid $id = null;

	#[ORM\OneToOne(inversedBy: 'apiKey')]
	#[ORM\JoinColumn(nullable: false)]
	#[OA\Property(title: 'The link associated with the API key')]
	private ?Link $link = null;

	#[ORM\Column(type: Types::STRING, length: 44)]
	#[OA\Property(title: 'The API key')]
	private ?string $key = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
	#[OA\Property(title: 'The creation date of the API key')]
	private ?DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The last update date of the API key')]
	private ?DateTimeImmutable $updatedAt = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
	#[OA\Property(title: 'The expiration date of the API key')]
	private ?DateTimeImmutable $expiresAt = null;

	/**
	 * Création des certaines propriétés de l'entité.
	 * @see https://github.com/symfony/symfony/discussions/53331
	 */
	public function __construct()
	{
		$this->id = Uuid::v7();
		$this->createdAt = new DateTimeImmutable();
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
	public function getKey(): ?string
	{
		return $this->key;
	}

	public function setKey(?string $key): static
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Définition ou récupération de la date de création de la clé API.
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
	 * Définition ou récupération de la date de dernière modification de la clé API.
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
	 * Définition ou récupération de la date d'expiration de la clé API.
	 */
	public function getExpiresAt(): ?DateTimeImmutable
	{
		return $this->expiresAt;
	}

	public function setExpiresAt(DateTimeImmutable $expiresAt): static
	{
		$this->expiresAt = $expiresAt;

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
			'key' => $this->getKey(),
			'link' => $this->getLink()?->toArray(),
			'createdAt' => $this->getCreatedAt(),
			'updatedAt' => $this->getUpdatedAt(),
			'expiresAt' => $this->getExpiresAt()
		];
	}
}
