<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use OpenApi\Attributes as OA;
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
	#[OA\Property(title: 'The unique identifier of the link')]
	private ?Uuid $id = null;

	#[ORM\Column(type: Types::TEXT)]
	#[OA\Property(title: 'The shortened URL')]
	#[Assert\Url(requireTld: true)]
	#[Assert\NotNull]
	#[Assert\NotBlank]
	private ?string $url = null;

	#[ORM\Column(type: Types::STRING, length: 50, unique: true)]
	#[OA\Property(title: 'The slug of the link')]
	#[Assert\Regex(pattern: '/^[a-zA-Z0-9-]+$/')]
	#[Assert\Length(min: 1, max: 50)]
	private ?string $slug = null;

	#[ORM\Column(type: Types::BOOLEAN)]
	#[OA\Property(title: 'The activation state of the link')]
	private ?bool $enabled = true;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	#[OA\Property(title: 'The creation date of the link')]
	private ?DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The last update date of the link')]
	private ?DateTimeImmutable $updatedAt = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	#[OA\Property(title: 'The last visit date of the link')]
	private ?DateTimeImmutable $visitedAt = null;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The expiration date of the link')]
	#[Assert\Range(min: 'tomorrow', max: '+1 year')]
	private ?DateTimeImmutable $expiresAt = null;

	/** @var Collection<int, Report> */
	#[ORM\OneToMany(mappedBy: "link", targetEntity: Report::class, orphanRemoval: true, cascade: ["persist", "remove"])]
	#[OA\Property(title: 'The user reports of the link')]
	private Collection $reports;

	#[ORM\OneToOne(mappedBy: "link", targetEntity: ApiKey::class, orphanRemoval: true, cascade: ["persist", "remove"])]
	#[OA\Property(title: 'The API key associated with the link')]
	private ApiKey $apiKey;

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
	 * Définition ou récupération de la date de création du lien.
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
	 * Définition ou récupération de la date de dernière modification du lien.
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
	 * Définition ou récupération de la date de dernière visite du lien.
	 */
	public function getVisitedAt(): ?DateTimeImmutable
	{
		return $this->visitedAt;
	}

	public function setVisitedAt(?DateTimeImmutable $visitedAt): static
	{
		$this->visitedAt = $visitedAt;

		return $this;
	}

	/**
	 * Définition ou récupération de la date d'expiration du lien.
	 */
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
	 * Récupération des signalements du lien raccourci.
	 * @return Collection<int, Report>
	 */
	public function getReports()
	{
		return $this->reports;
	}

	/**
	 * Récupération ou définition de la clé API.
	 */
	public function getApiKey()
	{
		return $this->apiKey;
	}

	public function setApiKey(ApiKey $apiKey): static
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * Conversion de l'entité en tableau.
	 * @return array<string, mixed>
	 */
	public function toArray(bool $firstTime = false)
	{
		$data = [
			'id' => $this->getId(),
			'url' => $this->getUrl(),
			'slug' => $this->getSlug(),
			'enabled' => $this->getEnabled(),
			'createdAt' => $this->getCreatedAt(),
			'expiresAt' => $this->getExpiresAt()
		];

		if ($firstTime)
		{
			// La clé API est affichée uniquement lors de la première création du lien
			// et non lors de la récupération des informations du lien.
			$data['apiKey'] = $this->getApiKey()->getKey();

			return $data;
		}

		// Lorsqu'on demande les informations d'un lien après sa création, on peut
		//  supposer qu'il peut avoir été mis à jour ou visité.
		$data['updatedAt'] = $this->getUpdatedAt();
		$data['createdAt'] = $this->getCreatedAt();

		return $data;
	}
}
