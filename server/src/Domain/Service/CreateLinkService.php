<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use const App\LOG_FUNCTION;

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
		private readonly LoggerInterface $logger,
		private readonly ValidatorInterface $validator,
		private readonly TranslatorInterface $translator,
		private readonly HttpClientInterface $httpClient,
		private readonly EntityManagerInterface $entityManager,
	) {
		$this->repository = $this->entityManager->getRepository(Link::class);
	}

	/**
	 * Valide les informations du lien raccourci.
	 * @phpstan-assert !null $link->getUrl()
	 * @phpstan-assert !null $link->getSlug()
	 */
	private function validateLink(Link $link): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$errors = [];
		$violations = $this->validator->validate($link);

		foreach ($violations as $violation) {
			$errors[$violation->getPropertyPath()][] = [
				'code' => $violation->getConstraint()->getErrorName($violation->getCode()),
				'message' => $violation->getMessage()
			];
		}

		if (!empty($errors))
		{
			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérifie si une URL est accessible et valide.
	 */
	public function checkUrl(string $url): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		try {
			$errors = [];
			$response = $this->httpClient->request('GET', $url, [
				'timeout' => 5,
			]);

			if ($response->getStatusCode() !== 200) {
				$errors['url'][] = [
					'code' => 'UNREACHABLE_URL_ERROR',
					'message' => $this->translator->trans('link.unreachable_url')
				];

				throw new DataValidationException($errors);
			}
		} catch (TransportExceptionInterface $exception) {
			$errors['url'][] = [
				'code' => 'invalid_url',
				'message' => $exception->getMessage()
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérifie si un slug existe déjà dans la base de données.
	 */
	private function checkSlug(string $slug): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$result = $this->repository->findOneBy(['slug' => $slug]);

		if (!empty($result))
		{
			$errors = [];
			$errors['slug'][] = [
				'code' => 'DUPLICATE_SLUG_ERROR',
				'message' => $this->translator->trans('slug.already_used')
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Création d'un slug aléatoire.
	 */
	private function createRandomSlug(): string
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$slug = '';
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		for ($i = 0; $i < mt_rand(5, 20); $i++)
		{
			$slug .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		return $slug;
	}

	/**
	 * Création d'un lien raccourci.
	 */
	public function createLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$url = $request->request->get('url');
		$slug = $request->request->get('slug', $this->createRandomSlug());
		$expiration = $request->request->get('expiration');
		$currentDate = new DateTime();

		$link = new Link();
		$link->setUrl(is_string($url) ? trim($url) : null);
		$link->setSlug(is_string($slug) ? trim($slug) : null);
		$link->setExpiration(is_string($expiration) ? new DateTime($expiration) : null);
		$link->setCreatedAt($currentDate);
		$link->setVisitedAt($currentDate);

		$this->validateLink($link);
		$this->checkUrl($link->getUrl());
		$this->checkSlug($link->getSlug());

		$this->repository->create($link, true);

		return $link;
	}
}