<?php

namespace App\Domain\Service;

use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use App\Domain\Factory\ApiKeyFactory;
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

		$url = $request->request->getString('url');
		$slug = $request->request->getString('slug', $this->createRandomSlug());
		$expiration = $request->request->getString('expiration');

		$link = LinkFactory::create($url, $slug, $expiration);
		$apiKey = ApiKeyFactory::create($link);
		$link->setApiKey($apiKey);

		$this->validateLink($link);
		$this->checkUrl($link->getUrl());
		$this->checkSlug($link->getSlug());

		$this->repository->save($link, true);

		return $link;
	}
}