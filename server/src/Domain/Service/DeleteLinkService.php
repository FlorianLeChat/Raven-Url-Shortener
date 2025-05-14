<?php

namespace App\Domain\Service;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use const App\LOG_FUNCTION;

/**
 * Service de suppression de liens raccourcis.
 */
final class DeleteLinkService extends BaseLinkService
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
	 * Suppression d'un lien raccourci.
	 */
	public function deleteLink(Request $request): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkApiKey($this->link, $request);
		$this->checkEnabled($this->link);
		$this->checkForReports($this->link);

		$this->repository->remove($this->link, true);

		return $this->link;
	}
}