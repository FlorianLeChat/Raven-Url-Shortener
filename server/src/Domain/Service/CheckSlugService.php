<?php

namespace App\Domain\Service;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;

use const App\LOG_FUNCTION;

/**
 * Service de vérification d'un slug personnalisé.
 */
final class CheckSlugService
{
	/**
	 * Répertoire des méthodes pour les liens raccourcis.
	 */
	private readonly LinkRepository $repository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->repository = $entityManager->getRepository(Link::class);
	}

	/**
	 * Vérification de la disponibilité d'un slug personnalisé.
	 */
	public function checkSlug(Request $request): bool
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$slug = $request->request->get("slug");
		$result = $this->repository->findOneBy(["slug" => $slug]);

		return empty($result);
	}
}