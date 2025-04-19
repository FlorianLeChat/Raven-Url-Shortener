<?php

namespace App\Domain\Service;

use DateTime;
use App\Domain\Entity\Link;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;

use const App\LOG_FUNCTION;

/**
 * Service de création de liens raccourcis.
 */
final class CreateLinkService extends BaseLinkService
{
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

		$this->repository->save($link, true);

		return $link;
	}
}