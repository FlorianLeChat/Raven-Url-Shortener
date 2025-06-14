<?php

namespace App\Action;

use App\Kernel;
use Psr\Log\LoggerInterface;
use App\Domain\Service\CheckSlugService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour la vérification d'un slug personnalisé.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
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
	#[Route('/slug', methods: ['POST'])]
	public function checkSlug(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new CheckSlugService($this->logger, $this->entityManager);

		$isAvailable = $service->checkSlug($request);

		return new JsonResponse(['available' => $isAvailable], JsonResponse::HTTP_OK);
	}
}