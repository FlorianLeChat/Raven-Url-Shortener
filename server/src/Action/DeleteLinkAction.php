<?php

namespace App\Action;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\DeleteLinkService;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la suppression d'un lien raccourci.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class DeleteLinkAction extends AbstractController
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
	 * Suppression d'un lien raccourci.
	 */
	#[Route('/link/{id}', methods: ['DELETE'], requirements: ['id' => Requirement::UUID_V7])]
	#[Route('/link/{slug}', methods: ['DELETE'], requirements: ['slug' => Requirement::ASCII_SLUG])]
	#[Security(name: 'ApiKeyAuth')]
	#[OA\Delete(
		tags: ['Link'],
		summary: 'Delete a short link',
		description: 'Delete a short link by its UUID or its custom slug.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\Parameter(name: 'id', in: 'path', description: 'The UUID of the link to delete.', schema: new OA\Schema(type: 'string', format: 'uuid')),
			new OA\Parameter(name: 'slug', in: 'path', description: 'The custom slug of the link to delete.', schema: new OA\Schema(type: 'string'))
		],
		responses: [
			new OA\Response(response: Response::HTTP_NO_CONTENT, description: 'Link deleted successfully'),
			new OA\Response(response: Response::HTTP_BAD_REQUEST, description: 'Link data deletion failed', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpBadRequest'
			)),
			new OA\Response(response: Response::HTTP_FORBIDDEN, description: 'Link deletion forbidden', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpForbidden'
			)),
			new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Link not found', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpNotFound'
			)),
			new OA\Response(response: Response::HTTP_TOO_MANY_REQUESTS, description: 'Link deletion rate limit exceeded', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpTooManyRequests'
			)),
			new OA\Response(response: Response::HTTP_INTERNAL_SERVER_ERROR, description: 'Internal server error', content: new OA\JsonContent(
				ref: '#/components/schemas/HttpInternalServerError'
			))
		]
	)]
	public function deleteLink(Request $request, Link $link): Response
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new DeleteLinkService(
			$link,
			$this->logger,
			$this->validator,
			$this->translator,
			$this->httpClient,
			$this->entityManager
		);

		$service->deleteLink($request);

		return new Response(status: Response::HTTP_NO_CONTENT);
	}
}