<?php

namespace App\Action;

use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour la création d'un lien raccourci.
 */
final class CreateLinkAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly ValidatorInterface $validator,
		private readonly EntityManagerInterface $entityManager
	) {}

	/**
	 * Création d'un lien raccourci.
	 */
	#[Route("/api/link", name: "create_link", methods: ["POST"], stateless: true)]
	public function createLink(Request $request): JsonResponse
	{
		$service = new CreateLinkService($this->validator, $this->entityManager);

		$link = $service->createLink($request);

		return new JsonResponse($link, JsonResponse::HTTP_CREATED);
	}
}