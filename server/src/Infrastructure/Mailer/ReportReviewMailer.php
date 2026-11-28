<?php

namespace App\Infrastructure\Mailer;

use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Emails the reporter the outcome of the review of their report, in the language of the report request.
 * The templates are the `report.<status>.*` keys of the `emails` translation domain (`locales/emails.*.php`).
 * The email names the link by its slug only: the target URL of an accepted report must not become a clickable link.
 * Outcomes are queued during the request and sent on `kernel.terminate` (see `config/services.php`), once FrankenPHP
 *  has sent the response: a slow SMTP server no longer delays the administrator. A review from the console
 *  (see ReportCommand) sends them on `console.terminate`.
 */
final class ReportReviewMailer
{
    /**
     * @var Report[] Reports whose outcome waits for the end of the request.
     */
    private array $queuedReports = [];

    /**
     * @param bool $isSmtpEnabled Whether emails can be sent (`SMTP_ENABLED`).
     */
    public function __construct(
        private readonly bool $isSmtpEnabled,
        private readonly LoggerInterface $logger,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator
    ) {
    }

    /**
     * Builds the email of the review outcome in the locale of the report.
     */
    private function createEmail(Report $report, string $address): Email
    {
        $locale = $report->getLocale();
        $status = $report->getStatus()->value;
        $parameters = ['%slug%' => $report->getLink()->getSlug()];

        $subject = $this->translator->trans(sprintf('report.%s.subject', $status), $parameters, 'emails', $locale);
        $body = $this->translator->trans(sprintf('report.%s.body', $status), $parameters, 'emails', $locale);

        return (new Email())
            ->to(new Address($address))
            ->subject($subject)
            ->text($body);
    }

    /**
     * Sends the outcome when SMTP is enabled.
     * A transport failure is logged without failing the review, which is already saved.
     */
    private function sendOutcome(Report $report, string $address): void
    {
        if (!$this->isSmtpEnabled) {
            $this->logger->warning('SMTP is disabled: the outcome of report {id} is not emailed.', [
                'id' => $report->getId()->toRfc4122()
            ]);

            return;
        }

        try {
            $this->mailer->send($this->createEmail($report, $address));
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Failed to email the outcome of report {id}: {message}', [
                'id' => $report->getId()->toRfc4122(),
                'message' => $exception->getMessage()
            ]);
        }
    }

    /**
     * Queues the outcome of a reviewed report, when the reporter left an email address.
     */
    public function queueOutcome(Report $report): void
    {
        if ($report->getEmail() !== null) {
            $this->queuedReports[] = $report;
        }
    }

    /**
     * Sends the queued outcomes and empties the queue, which a FrankenPHP worker keeps between requests.
     */
    public function sendQueuedOutcomes(): void
    {
        $reports = $this->queuedReports;
        $this->queuedReports = [];

        foreach ($reports as $report) {
            $this->sendOutcome($report, (string) $report->getEmail());
        }
    }
}
