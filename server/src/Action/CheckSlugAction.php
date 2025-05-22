<?php

namespace App\Action;

use Psr\Log\LoggerInterface;
use OpenApi\Attributes as OA;
use App\Domain\Service\CheckSlugService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la vérification d'un slug personnalisé.
 */
#[Route('/api/v{version}', stateless: true, requirements: ['version' => '1'])]
final class CheckSlugAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Vérification de la disponibilité d'un slug personnalisé.
	 */
	#[Route('/slug', methods: ['POST'])]
	#[OA\Post(
		tags: ['Slug'],
		summary: 'Check slug availability',
		description: 'Check if a custom slug is available.',
		parameters: [
			new OA\Parameter(name: 'version', in: 'path', description: 'The API version to use.', schema: new OA\Schema(type: 'string')),
			new OA\RequestBody(content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'slug', type: 'string', description: 'The slug to check for availability.'),
			]))
		],
		responses: [
			new OA\Response(response: JsonResponse::HTTP_OK, description: 'Slug is available or not', content: new OA\JsonContent(type: 'object', properties: [
				new OA\Property(property: 'available', type: 'boolean', description: 'True if the slug is available, false otherwise.'),
			]))
		],
	)]
	public function checkSlug(Request $request): JsonResponse
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$service = new CheckSlugService($this->logger, $this->entityManager);

		$isAvailable = $service->checkSlug($request);

		return new JsonResponse(['available' => $isAvailable], JsonResponse::HTTP_OK);
	}
}