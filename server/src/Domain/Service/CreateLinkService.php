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
	 * Création d'un slug aléatoire.
	 */
	private function createRandomSlug(): string
	{
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

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
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$payload = $request->getPayload();

		$url = $payload->getString('url');
		$slug = $payload->getString('slug', $this->createRandomSlug());
		$password = $payload->getString('password') ?: null;
		$expiration = $payload->getString('expiration') ?: null;
		$customDomain = $payload->getString('custom-domain') ?: null;
		$apiManagement = $payload->getBoolean('api-management');

		$link = LinkFactory::create([
			'url' => $url,
			'slug' => $slug,
			'password' => $password,
			'expiration' => $expiration,
			'custom-domain' => $customDomain
		]);

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