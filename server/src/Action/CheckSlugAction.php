<?php

namespace App\Action;

use Psr\Log\LoggerInterface;
use App\Domain\Service\CheckSlugService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la vérification d'un slug personnalisé.
 */
final class CheckSlugAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Vérification de la disponibilité d'un slug personnalisé.
	 */
	#[Route('/api/slug', methods: ['POST'], stateless: true)]
	public function checkSlug(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new CheckSlugService($this->logger, $this->entityManager);

		$isAvailable = $service->checkSlug($request);

		return new JsonResponse(['available' => $isAvailable], JsonResponse::HTTP_OK);
	}
}