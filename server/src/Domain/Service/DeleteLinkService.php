<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\Abstract\BaseLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Deletes a shortened link on behalf of its owner.
 */
final class DeleteLinkService extends BaseLinkService
{
    /**
     * @param Link $link Link reached by the request, resolved from its identifier or its slug.
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
     * Deletes the link once its API key is checked. A disabled or reported link cannot be deleted.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function deleteLink(Request $request): Link
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkApiKey($this->link, $request);
        $this->checkEnabled($this->link);
        $this->checkForReports($this->link);

        $this->repository->remove($this->link, true);

        return $this->link;
    }
}
