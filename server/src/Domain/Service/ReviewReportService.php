<?php

namespace App\Domain\Service;

use App\Kernel;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use App\Domain\Enum\ReportStatus;
use App\Domain\Factory\ReportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Infrastructure\Mailer\ReportReviewMailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Closes a report with the decision of an administrator, then tells the reporter the outcome.
 * Reserved to the administrators (see AdminRouteListener).
 */
final readonly class ReviewReportService
{
    /**
     * Decisions an administrator can send: a report cannot be put back to pending.
     */
    private const REVIEW_STATUSES = [ReportStatus::Accepted, ReportStatus::Rejected];

    public function __construct(
        private LoggerInterface $logger,
        private ReportReviewMailer $mailer,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws ConflictHttpException When an administrator has already reviewed the report.
     */
    private function checkPending(Report $report): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($report->getStatus() !== ReportStatus::Pending) {
            throw new ConflictHttpException($this->translator->trans('already_reviewed', domain: 'report'));
        }
    }

    /**
     * Reads the decision. The message reuses the translation of the Symfony `Choice` constraint.
     * @throws DataValidationException When `status` is neither `accepted` nor `rejected`.
     */
    private function getReviewStatus(string $value): ReportStatus
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $status = ReportStatus::tryFrom($value);

        if (!in_array($status, self::REVIEW_STATUSES, true)) {
            $message = 'The value you selected is not a valid choice.';
            $errors = [];
            $errors['status'][] = [
                'code' => 'NO_SUCH_CHOICE_ERROR',
                'value' => $value,
                'message' => $this->translator->trans($message, domain: 'validators')
            ];

            throw new DataValidationException($errors);
        }

        return $status;
    }

    /**
     * Accepts or rejects a pending report. An accepted report disables its link; a rejected one releases it
     *  once no other report is pending, and `trusted` then prevents any further report.
     * The reporter who left an email address receives the outcome once the response is sent, or once the console
     *  command ends (see ReportReviewMailer).
     * Called by the API (see ReviewReportAction) and the console (see ReportCommand).
     * @throws DataValidationException When `status` is neither `accepted` nor `rejected`.
     * @throws ConflictHttpException When the report has already been reviewed.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function reviewReport(Report $report, string $status, bool $trusted): Report
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkPending($report);

        ReportFactory::review($report, $this->getReviewStatus($status), $trusted);

        $this->entityManager->flush();
        $this->mailer->queueOutcome($report);

        return $report;
    }
}
