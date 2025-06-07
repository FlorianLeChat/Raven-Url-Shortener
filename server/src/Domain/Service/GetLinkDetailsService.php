<?php

namespace App\Domain\Service;

use App\Kernel;
use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Service\Abstract\BaseLinkService;

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
		$this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$link->setVisitedAt(new DateTimeImmutable());

		$this->repository->save($link, true);

		return $link;
	}
}