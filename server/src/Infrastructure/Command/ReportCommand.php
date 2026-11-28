<?php

namespace App\Infrastructure\Command;

use App\Domain\Entity\Report;
use Symfony\Component\Uid\Uuid;
use App\Domain\Enum\ReportStatus;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\ReviewReportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use App\Infrastructure\Repository\ReportRepository;
use Symfony\Component\Console\Output\OutputInterface;
use App\Infrastructure\Exception\DataValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Lists and reviews the reports from the server, as the administrator route does,
 *  without handing out the administrator API key: `app:report list|review`.
 */
#[AsCommand('app:report', 'Lists or reviews the user reports.')]
final class ReportCommand extends Command
{
    private const ACTIONS = ['list', 'review'];

    private const ALL_STATUSES = 'all';

    private const DATE_FORMAT = 'Y-m-d H:i:s T';

    /**
     * Characters of the reason shown in the table: a reason holds up to 500.
     */
    private const REASON_WIDTH = 60;

    public function __construct(
        private readonly ReviewReportService $service,
        private readonly ReportRepository $repository
    ) {
        parent::__construct();
    }

    /**
     * Declares the action, the report and decision of a review, and the filter of the list.
     */
    protected function configure(): void
    {
        $statuses = array_column(ReportStatus::cases(), 'value');

        $this
            ->addArgument('action', InputArgument::REQUIRED, implode(', ', self::ACTIONS))
            ->addArgument('id', InputArgument::OPTIONAL, 'UUID of the report to review')
            ->addArgument('decision', InputArgument::OPTIONAL, 'Decision of the review: accepted or rejected')
            ->addOption(
                'status',
                's',
                InputOption::VALUE_REQUIRED,
                sprintf('Status of the listed reports: %s or %s', implode(', ', $statuses), self::ALL_STATUSES),
                ReportStatus::Pending->value
            )
            ->addOption(
                'trusted',
                't',
                InputOption::VALUE_NONE,
                'Marks the link of a rejected report as trusted, so it cannot be reported again'
            );
    }

    /**
     * Reads a string argument or option, empty when it is missing.
     */
    private function getString(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Prints the reports of the requested status, the most recent first.
     */
    private function list(InputInterface $input, SymfonyStyle $io): int
    {
        $filter = $this->getString($input->getOption('status'));
        $status = ReportStatus::tryFrom($filter);

        if ($status === null && $filter !== self::ALL_STATUSES) {
            $io->error(sprintf('Unknown status "%s".', $filter));

            return Command::FAILURE;
        }

        $criteria = $status !== null ? ['status' => $status] : [];
        $rows = array_map(static fn (Report $report) => [
            $report->getId(),
            $report->getLink()->getSlug(),
            mb_strimwidth($report->getReason(), 0, self::REASON_WIDTH, '...'),
            $report->getEmail() ?? '-',
            $report->getStatus()->value,
            $report->getCreatedAt()->format(self::DATE_FORMAT)
        ], $this->repository->findBy($criteria, ['createdAt' => 'DESC']));

        $io->table(['Id', 'Link', 'Reason', 'Email', 'Status', 'Created'], $rows);

        return Command::SUCCESS;
    }

    /**
     * Records the decision on the report reached by its UUID.
     * @throws UniqueConstraintViolationException|ORMException
     */
    private function review(InputInterface $input, SymfonyStyle $io): int
    {
        $id = $this->getString($input->getArgument('id'));
        $report = Uuid::isValid($id) ? $this->repository->find($id) : null;

        if ($report === null) {
            $io->error(sprintf('No report has the identifier "%s".', $id));

            return Command::FAILURE;
        }

        try {
            $decision = $this->getString($input->getArgument('decision'));
            $this->service->reviewReport($report, $decision, (bool) $input->getOption('trusted'));
        } catch (DataValidationException) {
            $io->error('The decision must be accepted or rejected.');

            return Command::FAILURE;
        } catch (ConflictHttpException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Report %s %s.', $report->getId(), $report->getStatus()->value));

        return Command::SUCCESS;
    }

    /**
     * Lists the known actions.
     */
    private function failOnUnknownAction(SymfonyStyle $io): int
    {
        $io->error(sprintf('Unknown action. Use one of: %s.', implode(', ', self::ACTIONS)));

        return Command::FAILURE;
    }

    /**
     * Runs the action given as first argument.
     * @throws UniqueConstraintViolationException|ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return match ($input->getArgument('action')) {
            'list' => $this->list($input, $io),
            'review' => $this->review($input, $io),
            default => $this->failOnUnknownAction($io)
        };
    }
}
