<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\UpdateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour la mise à jour d'un lien raccourci.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class UpdateLinkAction extends AbstractController
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
	 * Mise à jour partielle d'un lien raccourci.
	 */
	#[Route('/link/{id}', methods: ['PATCH'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}', methods: ['PATCH'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function patchLink(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new UpdateLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$updatedLink = $service->patchLink($request);

		return new JsonResponse($updatedLink->toArray(), JsonResponse::HTTP_OK);
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	#[Route('/link/{id}', methods: ['PUT'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}', methods: ['PUT'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function replaceLink(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new UpdateLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$updatedLink = $service->replaceLink($request);

		return new JsonResponse($updatedLink->toArray(), JsonResponse::HTTP_OK);
	}
}