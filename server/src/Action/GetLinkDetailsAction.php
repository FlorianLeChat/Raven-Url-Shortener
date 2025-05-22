<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
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
	#[OA\Get(
		tags: ['Link'],
		summary: 'Get a short link details',
		description: 'Get the details of a short link by its UUID or its custom slug.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'path', description: 'The UUID of the link to fetch.', schema: new OA\Schema(type: 'string', format: 'uuid')),
			new OA\Parameter(name: 'slug', in: 'path', description: 'The custom slug of the link to fetch.', schema: new OA\Schema(type: 'string'))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_CREATED, description: 'Link details retrieved successfully', content: new OA\JsonContent(
				type: 'array',
				items: new OA\Items(ref: new Model(type: Link::class)))
			),
			new OA\Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Link not found', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpNotFound'
			)),
			new OA\Response(response: JsonResponse::HTTP_TOO_MANY_REQUESTS, description: 'Link fetch rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
	public function getLinkDetails(Link $link): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		return new JsonResponse($link->toArray(), JsonResponse::HTTP_OK);
	}
}