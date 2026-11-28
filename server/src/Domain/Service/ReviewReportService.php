<?php

namespace App\Domain\Service;

use App\Kernel;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use App\Domain\Enum\ReportStatus;
use App\Domain\Factory\ReportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Mailer\ReportReviewMailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Closes a report with the decision of an administrator, then tells the reporter the outcome.
 * Administrators are recognized by the private API key (`APP_PRIVATE_API_KEY`) until the API gets
 *  an authentication of its own.
 */
final readonly class ReviewReportService
{
    /**
     * Decisions an administrator can send: a report cannot be put back to pending.
     */
    private const REVIEW_STATUSES = [ReportStatus::Accepted, ReportStatus::Rejected];

    /**
     * @param string $privateApiKey The private API key (`APP_PRIVATE_API_KEY`), which also identifies
     *  the administrators. Empty, it refuses every request.
     */
    public function __construct(
        private string $privateApiKey,
        private LoggerInterface $logger,
        private ReportReviewMailer $mailer,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Compares the `Authorization: Bearer <key>` header with the private API key, in constant time.
     * @throws UnauthorizedHttpException When the header is missing.
     * @throws AccessDeniedHttpException When the key does not match, or no private API key is configured.
     */
    private function checkPrivateApiKey(Request $request): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $headerApiKey = $request->headers->get('Authorization');

        if (empty($headerApiKey)) {
            throw new UnauthorizedHttpException('Bearer', $this->translator->trans('api_key.missing'));
        }

        $headerApiKey = str_replace('Bearer ', '', $headerApiKey);
        $isConfigured = $this->privateApiKey !== '';
        $isValidApiKey = $isConfigured && hash_equals($this->privateApiKey, $headerApiKey);

        if (!$isValidApiKey) {
            throw new AccessDeniedHttpException($this->translator->trans('api_key.invalid'));
        }
    }

    /**
     * @throws ConflictHttpException When an administrator has already reviewed the report.
     */
    private function checkPending(Report $report): void
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($report->getStatus() !== ReportStatus::Pending) {
            throw new ConflictHttpException($this->translator->trans('report.already_reviewed'));
        }
    }

    /**
     * Reads the decision of the request body. The message reuses the translation of the Symfony `Choice` constraint.
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
     * The reporter who left an email address receives the outcome once the response is sent.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function reviewReport(Request $request, Report $report): Report
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkPrivateApiKey($request);
        $this->checkPending($report);

        $payload = $request->getPayload();
        $status = $this->getReviewStatus($payload->getString('status'));
        $trusted = $payload->getBoolean('trusted');

        ReportFactory::review($report, $status, $trusted);

        $this->entityManager->flush();
        $this->mailer->queueOutcome($report);

        return $report;
    }
}
