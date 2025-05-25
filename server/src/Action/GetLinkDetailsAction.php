<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
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
		private readonly LoggerInterface $logger
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

		return new JsonResponse($link->toArray(), JsonResponse::HTTP_OK);
	}
}