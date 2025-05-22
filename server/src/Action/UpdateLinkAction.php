<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\UpdateLinkService;
use Nelmio\ApiDocBundle\Attribute\Security;
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
	#[Security(name: 'ApiKeyAuth')]
	#[OA\Patch(
		tags: ['Link'],
		summary: 'Update a specific information of a short link',
		description: 'Update a specific information of a short link by its UUID or its custom slug.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'path', description: 'The UUID of the link to report.', schema: new OA\Schema(type: 'string', format: 'uuid')),
			new OA\Parameter(name: 'slug', in: 'path', description: 'The custom slug of the link to report.', schema: new OA\Schema(type: 'string')),
			new OA\RequestBody(request: 'Link', description: 'The link to update.', content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'field', type: 'string', description: 'The field to update.'),
				new OA\Property(property: 'value', type: 'string', description: 'The new value for the field.')
			]))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_CREATED, description: 'Link updated successfully', content: new OA\JsonContent(
				type: 'array',
				items: new OA\Items(ref: new Model(type: Link::class)))
			),
			new OA\Response(response: JsonResponse::HTTP_BAD_REQUEST, description: 'Link data validation failed', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpBadRequest'
			)),
			new OA\Response(response: JsonResponse::HTTP_FORBIDDEN, description: 'Link update forbidden', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpForbidden'
			)),
			new OA\Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Link not found', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpNotFound'
			)),
			new OA\Response(response: JsonResponse::HTTP_TOO_MANY_REQUESTS, description: 'Link update rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
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
	#[Route('/link/{id}', methods: ['PUT'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}', methods: ['PUT'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	#[Security(name: 'ApiKeyAuth')]
	#[OA\Put(
		tags: ['Link'],
		summary: 'Update a whole short link',
		description: 'Update a whole short link by its UUID or its custom slug.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'path', description: 'The UUID of the link to report.', schema: new OA\Schema(type: 'string', format: 'uuid')),
			new OA\Parameter(name: 'slug', in: 'path', description: 'The custom slug of the link to report.', schema: new OA\Schema(type: 'string')),
			new OA\RequestBody(request: 'Link', description: 'The link to update.', content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'url', type: 'string', description: 'The URL to shorten.'),
				new OA\Property(property: 'slug', type: 'string', nullable: true, description: 'The custom slug for the short link.'),
				new OA\Property(property: 'expiration', type: 'string', nullable: true, format: 'date-time', description: 'The expiration date for the short link.')
			]))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_CREATED, description: 'Link updated successfully', content: new OA\JsonContent(
				type: 'array',
				items: new OA\Items(ref: new Model(type: Link::class)))
			),
			new OA\Response(response: JsonResponse::HTTP_BAD_REQUEST, description: 'Link data validation failed', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpBadRequest'
			)),
			new OA\Response(response: JsonResponse::HTTP_FORBIDDEN, description: 'Link update forbidden', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpForbidden'
			)),
			new OA\Response(response: JsonResponse::HTTP_NOT_FOUND, description: 'Link not found', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpNotFound'
			)),
			new OA\Response(response: JsonResponse::HTTP_TOO_MANY_REQUESTS, description: 'Link update rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: JsonResponse::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
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