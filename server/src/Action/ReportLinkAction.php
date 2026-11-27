<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\ReportLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint letting a visitor report a shortened link to the administrators.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class ReportLinkAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ReportLinkService $service
    ) {
    }

    /**
     * Creates a report on the link reached by its identifier or its slug and answers it with a 201 status.
     * @throws UniqueConstraintViolationException|ORMException
     */
    #[Route('/link/{id}/report', requirements: ['id' => Requirement::UUID_V7], methods: ['POST'])]
    #[Route('/link/{slug}/report', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['POST'])]
    public function createReport(Request $request, Link $link): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $report = $this->service->createReport($request, $link);

        return new JsonResponse($report->toArray(), Response::HTTP_CREATED);
    }
}
