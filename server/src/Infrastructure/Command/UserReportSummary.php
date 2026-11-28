<?php

namespace App\Infrastructure\Command;

use App\Domain\Entity\Report;
use App\Domain\Enum\ReportStatus;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Daily task emailing the administrator the number of reports waiting for a review.
 */
#[AsPeriodicTask('1 day', from: 'midnight')]
#[AsCommand('app:reports-summary', 'Creates a summary of user reports and sends it by email.')]
final class UserReportSummary extends Command
{
    /**
     * @param bool $isSmtpEnabled Whether emails can be sent (`SMTP_ENABLED`).
     * @param string $smtpUsername Address receiving the summary, which is also the SMTP account (`SMTP_USERNAME`).
     */
    public function __construct(
        private readonly bool $isSmtpEnabled,
        private readonly string $smtpUsername,
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    /**
     * Builds the summary email for the given number of reports.
     */
    private function createEmail(int $count): Email
    {
        return (new Email())
            ->to(new Address($this->smtpUsername))
            ->text(sprintf('There are %d report(s) to review.', $count))
            ->subject('Report summary');
    }

    /**
     * Sends the email and turns a transport failure into a failed command.
     * @return int `Command::SUCCESS` or `Command::FAILURE`.
     */
    private function sendEmail(Email $email, SymfonyStyle $io): int
    {
        try {
            $this->mailer->send($email);

            $io->success('Email sent successfully.');

            return Command::SUCCESS;
        } catch (TransportExceptionInterface $error) {
            $io->error(sprintf('Failed to send email: %s', $error->getMessage()));

            return Command::FAILURE;
        }
    }

    /**
     * Sends the summary only when pending reports exist and SMTP is enabled. Neither case fails the command.
     * Reviewed reports are left out: they no longer wait for an administrator.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(Report::class);
        $count = $repository->count(['status' => ReportStatus::Pending]);

        if ($count === 0) {
            $output->writeln('No user reports found.');
            return Command::SUCCESS;
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Summary of %d user report(s)', $count));

        if (!$this->isSmtpEnabled) {
            $io->error('SMTP is disabled.');
            return Command::SUCCESS;
        }

        return $this->sendEmail($this->createEmail($count), $io);
    }
}
