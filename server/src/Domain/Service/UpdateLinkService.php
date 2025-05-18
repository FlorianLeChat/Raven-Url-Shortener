<?php

namespace App\Domain\Service;

use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
		protected Link $link,
		LoggerInterface $logger,
		ValidatorInterface $validator,
		TranslatorInterface $translator,
		HttpClientInterface $httpClient,
		EntityManagerInterface $entityManager,
	) {
		parent::__construct($logger, $validator, $translator, $httpClient, $entityManager);
	}

	/**
	 * Mise à jour partielle d'un lien raccourci.
	 */
	public function patchLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkApiKey($this->link, $request);
		$this->checkEnabled($this->link);
		$this->checkForReports($this->link);

		$payload = $request->getPayload();
		$field = $payload->getString('url');
		$value = $payload->getString('value');

		if ($field === 'slug')
		{
			$this->checkSlug($value);
		}

		$this->link = LinkFactory::patch($this->link, $field, $value);

		$this->validateLink($this->link);
		$this->checkUrl($this->link->getUrl());

		$this->repository->save($this->link, true);

		return $this->link;
	}

	/**
	 * Mise à jour complète d'un lien raccourci.
	 */
	public function replaceLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkApiKey($this->link, $request);
		$this->checkEnabled($this->link);
		$this->checkForReports($this->link);

		$url = $request->request->getString('url', $this->link->getUrl() ?? '');
		$slug = $request->request->getString('slug', $this->link->getSlug() ?? '');
		$expiration = $request->request->getString('expiration', $this->link->getExpiresAt()?->format('Y-m-d H:i:s') ?? '');

		if ($this->link->getSlug() !== $slug)
		{
			$this->checkSlug($slug);
		}

		$this->link = LinkFactory::update($this->link, $url, $slug, $expiration);

		$this->validateLink($this->link);
		$this->checkUrl($this->link->getUrl());

		$this->repository->save($this->link, true);

		return $this->link;
	}
}