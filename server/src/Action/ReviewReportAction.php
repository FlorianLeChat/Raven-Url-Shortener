<?php

namespace App\Action;

use App\Kernel;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\ReviewReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Requirement\Requirement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint letting an administrator accept or reject the report of a link.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class ReviewReportAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ReviewReportService $service
    ) {
    }

    /**
     * Records the decision on the report reached by its identifier and answers the closed report.
     * @throws UniqueConstraintViolationException|ORMException
     */
    #[Route('/report/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['PATCH'])]
    public function reviewReport(Request $request, Report $report): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $reviewedReport = $this->service->reviewReport($request, $report);

        return new JsonResponse($reviewedReport->toArray(), Response::HTTP_OK);
    }
}
