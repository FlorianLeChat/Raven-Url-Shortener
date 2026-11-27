<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\Abstract\BaseLinkService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Deletes a shortened link on behalf of its owner.
 */
final class DeleteLinkService extends BaseLinkService
{
    /**
     * Deletes the link once its API key is checked. A disabled or reported link cannot be deleted.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function deleteLink(Request $request, Link $link): Link
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($link, $request);
        $this->checkEnabled($link);
        $this->checkForReports($link);

        $this->repository->remove($link, true);

        return $link;
    }
}
