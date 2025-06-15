<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Doctrine\Common\Collections\ArrayCollection;
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

	#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	#[OA\Property(title: 'The password to access the link')]
	#[Assert\Length(max: 255)]
	private ?string $password = null;

	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
	#[OA\Property(title: 'The activation state of the link')]
	private ?bool $enabled = true;

	#[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
	#[OA\Property(title: 'The trust state of the link')]
	private ?bool $trusted = false;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
	#[OA\Property(title: 'The creation date of the link')]
	private ?DateTimeImmutable $createdAt = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The last update date of the link')]
	private ?DateTimeImmutable $updatedAt = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The last visit date of the link')]
	private ?DateTimeImmutable $visitedAt = null;

	#[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
	#[OA\Property(title: 'The expiration date of the link')]
	#[Assert\Range(min: 'tomorrow', max: '+1 year')]
	private ?DateTimeImmutable $expiresAt = null;

	/** @var Collection<int, Report> */
	#[ORM\OneToMany(mappedBy: "link", targetEntity: Report::class, orphanRemoval: true, cascade: ["persist", "remove"])]
	#[OA\Property(title: 'The user reports of the link')]
	private Collection $reports;

	#[ORM\OneToOne(mappedBy: "link", targetEntity: ApiKey::class, orphanRemoval: true, cascade: ["persist", "remove"])]
	#[OA\Property(title: 'The API key associated with the link')]
	private ?ApiKey $apiKey = null;

	/**
	 * Création des certaines propriétés de l'entité.
	 * @see https://github.com/symfony/symfony/discussions/53331
	 */
	public function __construct()
	{
		$this->id = Uuid::v7();
		$this->reports = new ArrayCollection();
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
	 * Définition ou récupération du mot de passe du lien.
	 */
	public function getPassword(): ?string
	{
		return $this->password;
	}

	public function setPassword(?string $password): static
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * Définition ou récupération de l'état d'activation d'un lien.
	 */
	public function isEnabled(): ?bool
	{
		return $this->enabled;
	}

	public function setEnabled(?bool $enabled): static
	{
		$this->enabled = $enabled;

		return $this;
	}

	/**
	 * Définition ou récupération de l'état de confiance d'un lien.
	 */
	public function isTrusted(): ?bool
	{
		return $this->trusted;
	}

	public function setTrusted(?bool $trusted): static
	{
		$this->trusted = $trusted;

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
	 * Conversion de l'entité en tableau.
	 * @return array<string, mixed>
	 */
	public function toArray()
	{
		$data = [
			'id' => $this->getId(),
			'url' => $this->getUrl(),
			'slug' => $this->getSlug(),
			'enabled' => $this->isEnabled(),
			'trusted' => $this->isTrusted(),
			'reported' => count($this->getReports()) > 0,
			'createdAt' => $this->getCreatedAt(),
			'updatedAt' => $this->getUpdatedAt(),
			'visitedAt' => $this->getVisitedAt(),
			'expiresAt' => $this->getExpiresAt()
		];

		if ($this->getVisitedAt() === null)
		{
			// La clé API est affichée uniquement lors de la première visite du lien,
			//  pour éviter de l'exposer à chaque fois que l'on demande les informations du lien.
			$data['apiKey'] = $this->getApiKey()?->getKey();

			return $data;
		}

		return $data;
	}
}
