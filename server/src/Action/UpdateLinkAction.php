<?php

namespace App\Action;

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

use const App\LOG_FUNCTION;

/**
 * Action pour la mise à jour d'un lien raccourci.
 */
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
	#[Route('/api/link/{id}', methods: ['PATCH'], stateless: true, requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/api/link/{slug}', methods: ['PATCH'], stateless: true, requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function patchLink(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new UpdateLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$updatedLink = $service->patchLink($request);

		return new JsonResponse($updatedLink->toArray(), JsonResponse::HTTP_CREATED);
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	#[Route('/api/link/{id}', methods: ['PUT'], stateless: true, requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/api/link/{slug}', methods: ['PUT'], stateless: true, requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function replaceLink(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new UpdateLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$updatedLink = $service->replaceLink($request);

		return new JsonResponse($updatedLink->toArray(), JsonResponse::HTTP_CREATED);
	}
}