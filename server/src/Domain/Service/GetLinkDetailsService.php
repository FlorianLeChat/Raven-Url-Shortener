<?php

namespace App\Domain\Service;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Service\Abstract\BaseLinkService;

use const App\LOG_FUNCTION;

/**
 * Service de récupération des informations d'un lien raccourci.
 */
final class GetLinkDetailsService extends BaseLinkService
{
	/**
	 * Récupération des informations d'un lien raccourci.
	 */
	public function getLinkDetails(Link $link): Link
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$this->checkUrl($link->getUrl() ?? '');

		$link->setVisitedAt(new DateTimeImmutable());

		$this->repository->save($link, true);

		return $link;
	}
}