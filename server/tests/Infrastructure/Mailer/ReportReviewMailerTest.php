<?php

namespace App\Tests\Infrastructure\Mailer;

use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use App\Domain\Entity\Report;
use Symfony\Component\Mime\Email;
use App\Domain\Enum\ReportStatus;
use App\Domain\Factory\ReportFactory;
use Symfony\Component\Mailer\MailerInterface;
use App\Infrastructure\Mailer\ReportReviewMailer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Tests of ReportReviewMailer, with the translator of the container and a mocked mailer.
 */
final class ReportReviewMailerTest extends KernelTestCase
{
    /**
     * Creates a report on the `my-link` slug, reviewed with the given decision.
     */
    private function createReviewedReport(ReportStatus $status, ?string $email, string $locale): Report
    {
        $link = (new Link())->setSlug('my-link');

        return ReportFactory::review(ReportFactory::create($link, 'Phishing page', $email, $locale), $status);
    }

    /**
     * Creates the mailer service with SMTP enabled or not.
     */
    private function createMailer(bool $isSmtpEnabled, MailerInterface $mailer, LoggerInterface $logger): ReportReviewMailer
    {
        /** @var TranslatorInterface $translator */
        $translator = self::getContainer()->get(TranslatorInterface::class);

        return new ReportReviewMailer($isSmtpEnabled, $logger, $mailer, $translator);
    }

    public function testSendOutcomeInReporterLocale(): void
    {
        $emails = [];
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->exactly(2))->method('send')->willReturnCallback(
            static function (Email $email) use (&$emails): void {
                $emails[] = $email;
            }
        );

        $service = $this->createMailer(true, $mailer, $this->createMock(LoggerInterface::class));
        $service->queueOutcome($this->createReviewedReport(ReportStatus::Accepted, 'jane.doe@domain.com', 'en'));
        $service->queueOutcome($this->createReviewedReport(ReportStatus::Rejected, 'jane.doe@domain.com', 'fr'));
        $service->sendQueuedOutcomes();
        $service->sendQueuedOutcomes();

        self::assertSame('jane.doe@domain.com', $emails[0]->getTo()[0]->getAddress());
        self::assertSame('Your report has been accepted', $emails[0]->getSubject());
        self::assertStringContainsString('"my-link"', (string) $emails[0]->getTextBody());
        self::assertSame('Votre signalement a été examiné', $emails[1]->getSubject());
        self::assertStringContainsString('« my-link »', (string) $emails[1]->getTextBody());
    }

    public function testSendOutcomeWithoutEmailOrSmtp(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $anonymous = $this->createMailer(true, $mailer, $logger);
        $anonymous->queueOutcome($this->createReviewedReport(ReportStatus::Accepted, null, 'en'));
        $anonymous->sendQueuedOutcomes();

        $smtpDisabled = $this->createMailer(false, $mailer, $logger);
        $smtpDisabled->queueOutcome($this->createReviewedReport(ReportStatus::Accepted, 'jane.doe@domain.com', 'en'));
        $smtpDisabled->sendQueuedOutcomes();
    }

    public function testSendOutcomeWithMailerFailure(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('Connection refused'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with(
            self::stringContains('Failed to email the outcome'),
            self::callback(static fn (array $context): bool => $context['message'] === 'Connection refused')
        );

        $service = $this->createMailer(true, $mailer, $logger);
        $service->queueOutcome($this->createReviewedReport(ReportStatus::Rejected, 'jane.doe@domain.com', 'en'));
        $service->sendQueuedOutcomes();
    }
}
