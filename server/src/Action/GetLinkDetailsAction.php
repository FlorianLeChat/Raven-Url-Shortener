<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\GetLinkDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint reading the details of a shortened link, used by the client before any redirection.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class GetLinkDetailsAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly GetLinkDetailsService $service
    ) {
    }

    /**
     * Answers the link reached by its identifier or its slug and records the visit.
     * Expired and password protected links are checked by GetLinkDetailsService.
     * No cache attribute: the response keeps the `no-cache, private` default of Symfony, since it may follow
     *  a password check and each request records a visit.
     * @throws UniqueConstraintViolationException|ORMException
     */
    #[Route('/link/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['GET'])]
    #[Route('/link/{slug}', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function getLinkDetails(Request $request, Link $link): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $link = $this->service->getLinkDetails($request, $link);

        return new JsonResponse($link->toArray(), Response::HTTP_OK);
    }
}
