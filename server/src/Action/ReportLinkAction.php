<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use App\Domain\Entity\Report;
use Nelmio\ApiDocBundle\Attribute\Model;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\ReportLinkService;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

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
	#[Security(name: 'ApiKeyAuth')]
	#[OA\Post(
		tags: ['Report'],
		summary: 'Create a report for a short link',
		description: 'Create a report for a short link by its UUID or its custom slug.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'path', description: 'The UUID of the link to report.', schema: new OA\Schema(type: 'string', format: 'uuid')),
			new OA\Parameter(name: 'slug', in: 'path', description: 'The custom slug of the link to report.', schema: new OA\Schema(type: 'string')),
			new OA\RequestBody(request: 'Report', description: 'The report to create.', content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'reason', type: 'string', description: 'The reason for reporting the link.'),
				new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, description: 'The email of the reporter.')
			]))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_CREATED, description: 'Report created successfully', content: new OA\JsonContent(
				type: 'array',
				items: new OA\Items(ref: new Model(type: Report::class)))
			),
			new OA\Response(response: JsonResponse::HTTP_BAD_REQUEST, description: 'Report data validation failed', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpBadRequest'
			)),
			new OA\Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Link not found', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpNotFound'
			)),
			new OA\Response(response: JsonResponse::HTTP_CONFLICT, description: 'Report already exists', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpConflict'
			)),
			new OA\Response(response: JsonResponse::HTTP_TOO_MANY_REQUESTS, description: 'Report rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
	public function createReport(Request $request, Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

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