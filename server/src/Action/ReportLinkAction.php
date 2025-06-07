<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\ReportLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour le signalement d'un lien raccourci.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class ReportLinkAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly TranslatorInterface $translator,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Signalement d'un lien raccourci.
	 */
	#[Route('/link/{id}/report', methods: ['POST'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}/report', methods: ['POST'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	public function createReport(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new ReportLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->entityManager
		);

		$report = $service->createReport($request);

		return new JsonResponse($report->toArray(), JsonResponse::HTTP_CREATED);
	}
}