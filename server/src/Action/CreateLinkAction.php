<?php

namespace App\Action;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la création d'un lien raccourci.
 */
final class CreateLinkAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Création d'un lien raccourci.
	 */
	#[Route("/api/link", name: "create_link", methods: ["POST"], stateless: true)]
	public function createLink(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new CreateLinkService($this->logger, $this->validator, $this->entityManager);

		$link = $service->createLink($request);

		return new JsonResponse($link, JsonResponse::HTTP_CREATED);
	}
}