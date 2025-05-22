<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

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
	#[OA\Post(
		tags: ['Link'],
		summary: 'Create a new short link',
		description: 'Create a new short link, with optional custom slug and expiration date.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\RequestBody(request: 'Link', description: 'The link to shorten.', content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'url', type: 'string', description: 'The URL to shorten.'),
				new OA\Property(property: 'slug', type: 'string', nullable: true, description: 'The custom slug for the short link.'),
				new OA\Property(property: 'expiration', type: 'string', nullable: true, format: 'date-time', description: 'The expiration date for the short link.')
			]))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_CREATED, description: 'Link created successfully', content: new OA\JsonContent(
				type: 'array',
				items: new OA\Items(ref: new Model(type: Link::class)))
			),
			new OA\Response(response: JsonResponse::HTTP_BAD_REQUEST, description: 'Link data validation failed', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpBadRequest'
			)),
			new OA\Response(response: JsonResponse::HTTP_TOO_MANY_REQUESTS, description: 'Link creation rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
	public function createLink(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

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