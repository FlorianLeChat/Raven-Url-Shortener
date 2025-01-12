<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Service de création de liens raccourcis.
 */
final class CreateLinkService
{
	/**
	 * Répertoire des méthodes pour les liens raccourcis.
	 */
	private readonly LinkRepository $repository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly ValidatorInterface $validator,
		private readonly EntityManagerInterface $entityManager
	) {
		$this->repository = $entityManager->getRepository(Link::class);
	}

	/**
	 * Valide les informations du lien raccourci.
	 */
	private function validateLink(Link $link): void
	{
		$violations = $this->validator->validate($link);

		if (count($violations) > 0)
		{
			throw new BadRequestHttpException($violations->get(0)->getMessage());
		}
	}

	/**
	 * Création d'un lien raccourci.
	 */
	public function createLink(Request $request): Link
	{
		$link = new Link();
		$link->setUrl($request->request->get("url"));
		$link->setSlug($request->request->get("slug"));
		$link->setExpiration(new DateTime($request->request->get("expiration")));

		$this->validateLink($link);

		$this->repository->create($link, true);

		return $link;
	}
}