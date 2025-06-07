<?php

namespace App\Action;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour la création d'un lien raccourci.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class CreateLinkAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly TranslatorInterface $translator,
		private readonly HttpClientInterface $httpClient,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Création d'un lien raccourci.
	 */
	#[Route('/link', methods: ['POST'])]
	public function createLink(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new CreateLinkService(
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$link = $service->createLink($request);

		return new JsonResponse($link->toArray(true), JsonResponse::HTTP_CREATED);
	}
}