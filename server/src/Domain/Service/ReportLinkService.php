<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use App\Domain\Factory\ReportFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\ReportRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Files the report of a visitor against a link.
 */
final readonly class ReportLinkService
{
    private const MAX_REPORTS_PER_LINK = 3;
    private ReportRepository $repository;

    /**
     * @param Link $link Link reached by the request, resolved from its identifier or its slug.
     */
    public function __construct(
        private Link $link,
        private LoggerInterface $logger,
        private ValidatorInterface $validator,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $this->entityManager->getRepository(Report::class);
    }

    /**
     * Runs the entity constraints on the report.
     * @throws DataValidationException When at least one constraint fails, with every violation by field.
     */
    private function validateReport(Report $report): void
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $errors = [];
        $violations = $this->validator->validate($report);

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = [
                'code' => $violation->getConstraint()?->getErrorName($violation->getCode() ?? '') ?? '',
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage()
            ];
        }

        if (!empty($errors)) {
            throw new DataValidationException($errors);
        }
    }

    /**
     * @throws ConflictHttpException When this email address has already reported the link.
     */
    protected function checkReportExists(string $email): void
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $result = $this->repository->findOneBy(['link' => $this->link, 'email' => $email]);

        if (!empty($result)) {
            throw new ConflictHttpException($this->translator->trans('report.duplicated'));
        }
    }

    /**
     * @throws AccessDeniedHttpException When the link targets a trusted domain.
     */
    private function checkTrustedLink(Link $link): void
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if ($link->isTrusted()) {
            throw new AccessDeniedHttpException($this->translator->trans('report.trusted_link'));
        }
    }

    /**
     * @throws TooManyRequestsHttpException When the link already has the maximum number of reports.
     */
    private function checkMaximumReports(Link $link): void
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $reports = $this->repository->count(['link' => $link]);

        if ($reports >= self::MAX_REPORTS_PER_LINK) {
            throw new TooManyRequestsHttpException(message: $this->translator->trans('report.maximum_reached', [
                '%max%' => self::MAX_REPORTS_PER_LINK
            ]));
        }
    }

    /**
     * Validates and saves the report. The email address is optional; when given, it can report the link once.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function createReport(Request $request): Report
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->checkTrustedLink($this->link);
        $this->checkMaximumReports($this->link);

        $payload = $request->getPayload();

        $email = $payload->getString('email') ?: null;
        $reason = $payload->getString('reason');

        $report = ReportFactory::create($this->link, $reason, $email);

        $this->validateReport($report);

        if (!empty($report->getEmail())) {
            $this->checkReportExists($report->getEmail());
        }

        $this->repository->save($report, true);

        return $report;
    }
}
