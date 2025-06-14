<?php

namespace App\Domain\Service\Abstract;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$errors = [];
		$violations = $this->validator->validate($link);

		foreach ($violations as $violation) {
			$errors[$violation->getPropertyPath()][] = [
				'code' => $violation->getConstraint()?->getErrorName($violation->getCode() ?? '') ?? '',
				'value' => $violation->getInvalidValue(),
				'message' => $violation->getMessage()
			];
		}

		if (!empty($errors))
		{
			throw new DataValidationException($errors);
		}
	}

	/**
	 * Détecte si une requête a été bloquée par un pare-feu ou une limite de débit.
	 */
	private function isBlockedRequest(string $url, ResponseInterface $response): bool
	{
		$content = strtolower($response->getContent(false));
		$keywords = str_contains($content, 'access denied') || str_contains($content, 'verify you are human') ;
		$isBlocked = in_array($response->getStatusCode(), [403, 429]) || $keywords;

		if ($isBlocked)
		{
			$this->logger->warning('Unreachable URL due to firewall or rate limiting: ' . $url);
		}

		return $isBlocked;
	}

	/**
	 * Vérifie si une URL est accessible et valide.
	 */
	protected function checkUrl(string $url): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		try {
			$errors = [];
			$response = $this->httpClient->request('GET', $url, [
				'timeout' => 5,
				'max_redirects' => 1
			]);

			$isBlocked = $this->isBlockedRequest($url, $response);
			$isFailed = $response->getStatusCode() !== 200 && !$isBlocked;

			if ($isFailed)
			{
				$errors['url'][] = [
					'code' => 'UNREACHABLE_URL_ERROR',
					'value' => $url,
					'message' => $this->translator->trans('link.unreachable_url')
				];

				throw new DataValidationException($errors);
			}
		} catch (TransportExceptionInterface $exception) {
			$errors['url'][] = [
				'code' => 'UNREACHABLE_URL_ERROR',
				'value' => $url,
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
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$result = $this->repository->findOneBy(['slug' => $slug]);

		if (!empty($result))
		{
			$errors = [];
			$errors['slug'][] = [
				'code' => 'DUPLICATE_SLUG_ERROR',
				'value' => $slug,
				'message' => $this->translator->trans('slug.already_used')
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérification de l'état d'accès d'un lien raccourci.
	 */
	protected function checkEnabled(Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		if (!$link->isEnabled())
		{
			throw new AccessDeniedHttpException($this->translator->trans('link.disabled'));
		}
	}

	/**
	 * Vérification du nombre de signalements d'un lien raccourci.
	 */
	protected function checkForReports(Link $link): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$reports = $link->getReports();

		if (count($reports) > 0)
		{
			throw new AccessDeniedHttpException($this->translator->trans('link.reported'));
		}
	}

	/**
	 * Vérification de la validité de la clé API.
	 */
	protected function checkApiKey(Link $link, Request $request): void
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$headerApiKey = $request->headers->get('Authorization');

		if (empty($headerApiKey))
		{
			throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api_key.missing'));
		}

		$headerApiKey = str_replace('Bearer ', '', $headerApiKey);
		$storedApiKey = $link->getApiKey()?->getKey() ?? '';
		$isValidApiKey = preg_match('/^.{43}=$/', $headerApiKey) && hash_equals($storedApiKey, $headerApiKey);

		if (!$isValidApiKey)
		{
			throw new AccessDeniedHttpException($this->translator->trans('api_key.invalid'));
		}
	}
}