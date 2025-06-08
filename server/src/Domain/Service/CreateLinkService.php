<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use App\Domain\Factory\LinkFactory;
use App\Domain\Factory\ApiKeyFactory;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;

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
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$payload = $request->getPayload();

		$url = $payload->getString('url');
		$slug = $payload->getString('slug', $this->createRandomSlug());
		$expiration = $payload->getString('expiration');
		$apiManagement = $payload->getBoolean('api-management', false);

		$link = LinkFactory::create($url, $slug, $expiration);

		if ($apiManagement)
		{
			$apiKey = ApiKeyFactory::create($link);
			$link->setApiKey($apiKey);
		}

		$this->validateLink($link);
		$this->checkUrl($link->getUrl());
		$this->checkSlug($link->getSlug());

		$this->repository->save($link, true);

		return $link;
	}
}