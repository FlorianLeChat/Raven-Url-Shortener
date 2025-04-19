<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use const App\LOG_FUNCTION;

/**
 * Service de mise à jour de liens raccourcis.
 */
final class UpdateLinkService extends BaseLinkService
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		protected readonly Link $link,
		LoggerInterface $logger,
		ValidatorInterface $validator,
		TranslatorInterface $translator,
		HttpClientInterface $httpClient,
		EntityManagerInterface $entityManager,
	) {
		parent::__construct($logger, $validator, $translator, $httpClient, $entityManager);
	}

	/**
	 * Vérification de l'origine de la requête HTTP.
	 */
	private function checkRequestOrigin(Request $request): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$ipAddress = $request->getClientIp();
		$isLocalAddress =
			// Adresses IPv4/v6 de la boucle locale (localhost)
			$ipAddress === '127.0.0.1' || $ipAddress === '::1' ||
			// Bloc d'adresses IP privées en 192.168.x.x
			preg_match('/^192\.168\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 10.x.x.x
			preg_match('/^10\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 172.16.x.x à 172.31.x.x
			preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress);

		if (!$isLocalAddress)
		{
			throw new AccessDeniedHttpException('Only local requests are allowed for this endpoint.');
		}
	}

	/**
	 * Vérification de l'état d'accès d'un lien raccourci.
	 */
	private function checkEnabled(): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		if (!$this->link->getEnabled())
		{
			$errors = [];
			$errors['slug'][] = [
				'code' => 'DISABLED_LINK_ERROR',
				'message' => $this->translator->trans('link.disabled')
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Vérification du nombre de signalements d'un lien raccourci.
	 */
	private function checkForReports(): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$reports = $this->link->getReports();

		if (!empty($reports))
		{
			$errors = [];
			$errors['slug'][] = [
				'code' => 'REPORTED_LINK_ERROR',
				'message' => $this->translator->trans('link.reported')
			];

			throw new DataValidationException($errors);
		}
	}

	/**
	 * Remplacement de la valeur d'un champ d'un lien raccourci.
	 */
	private function replaceValueByField(string $field, string $value): void
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		match ($field)
		{
			'url' => $this->link->setUrl(trim($value)),
			'slug' => $this->link->setSlug(trim($value)),
			'visitedAt' => $this->link->setVisitedAt(new DateTime()),
			'expiration' => $this->link->setExpiration(is_string($value) ? new DateTime($value) : null),
			default => null
		};
	}

	/**
	 * Mise à jour partielle d'un lien raccourci.
	 * @internal Seules les requêtes locales sont autorisées.
	 */
	public function patchLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkRequestOrigin($request);

		// https://github.com/symfony/symfony/issues/59331
		$payload = json_decode($request->getContent(), true);
		$field = $payload['field'] ?? '';
		$value = $payload['value'] ?? '';

		$this->replaceValueByField($field, $value);
		$this->validateLink($this->link);
		$this->checkUrl($this->link->getUrl());

		if ($field === 'slug')
		{
			$this->checkSlug($this->link->getSlug());
		}

		$this->repository->save($this->link, true);

		return $this->link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	public function replaceLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkEnabled();
		$this->checkForReports();

		$url = $request->request->getString('url', $this->link->getUrl());
		$slug = $request->request->getString('slug', $this->link->getSlug());
		$expiration = $request->request->getString('expiration', $this->link->getExpiration()->format('Y-m-d H:i:s'));
		$currentDate = new DateTime();

		$this->link->setUrl(trim($url));
		$this->link->setSlug(trim($slug));
		$this->link->setExpiration(is_string($expiration) ? new DateTime($expiration) : null);
		$this->link->setUpdatedAt($currentDate);

		$this->validateLink($this->link);
		$this->checkUrl($this->link->getUrl());
		$this->checkSlug($this->link->getSlug());

		$this->repository->save($this->link, true);

		return $this->link;
	}
}