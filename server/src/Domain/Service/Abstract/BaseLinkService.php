<?php

namespace App\Domain\Service\Abstract;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

use const App\LOG_FUNCTION;

/**
 * Classe abstraite pour les services de liens raccourcis.
 */
abstract class BaseLinkService
{
	/**
	 * Répertoire des méthodes pour les liens raccourcis.
	 */
	protected readonly LinkRepository $repository;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		protected readonly LoggerInterface $logger,
		protected readonly ValidatorInterface $validator,
		protected readonly TranslatorInterface $translator,
		protected readonly HttpClientInterface $httpClient,
		protected readonly EntityManagerInterface $entityManager,
	) {
		$this->repository = $this->entityManager->getRepository(Link::class);
	}

	/**
	 * Valide les informations du lien raccourci.
	 * @phpstan-assert !null $link->getUrl()
	 * @phpstan-assert !null $link->getSlug()
	 */
	protected function validateLink(Link $link): void
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
	protected function checkUrl(string $url): void
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
				'code' => 'UNREACHABLE_URL_ERROR',
				'message' => $exception->getMessage()
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérifie si un slug existe déjà dans la base de données.
	 */
	protected function checkSlug(string $slug): void
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
	protected function createRandomSlug(): string
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
}