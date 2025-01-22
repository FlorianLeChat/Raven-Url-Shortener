<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la récupération des informations d'un lien raccourci.
 */
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
	#[Route("/api/link/{uuid}", methods: ["GET"], stateless: true)]
	#[Cache(public: true, max_age: 3600, mustRevalidate: true)]
	public function getLinkDetails(#[MapEntity(id: "uuid")] Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		return new JsonResponse($link->toArray(), JsonResponse::HTTP_OK);
	}
}