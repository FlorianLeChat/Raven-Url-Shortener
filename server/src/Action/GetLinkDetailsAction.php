<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\GetLinkDetailsService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la récupération des informations d'un lien raccourci.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class GetLinkDetailsAction extends AbstractController
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
	 * Récupération des informations d'un lien raccourci.
	 */
	#[Cache(public: true, maxage: 3600, mustRevalidate: true)]
	#[Route('/link/{id}', methods: ['GET'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}', methods: ['GET'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function getLinkDetails(Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new GetLinkDetailsService(
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$link = $service->getLinkDetails($link);

		return new JsonResponse($link->toArray(), JsonResponse::HTTP_OK);
	}
}