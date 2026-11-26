<?php

namespace App\Tests\Infrastructure\Command;

use App\Domain\Entity\Report;
use App\Tests\TestHelperTrait;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Console\Command\Command;
use App\Infrastructure\Command\UserReportSummary;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Tests of the `app:reports-summary` command, on the fixtures with SMTP disabled,
 *  then with a mocked mailer for the branches sending an email.
 */
final class UserReportSummaryTest extends KernelTestCase
{
    use TestHelperTrait;

    /**
     * Creates the command with an entity manager answering the given number of reports.
     */
    private function createCommand(int $reports, MailerInterface $mailer): UserReportSummary
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findAll')->willReturn(array_fill(0, $reports, new Report()));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        return new UserReportSummary(true, 'admin@domain.com', $mailer, $entityManager);
    }

    public function testExecuteWithSmtpDisabled(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        self::loadFixtures($kernel->getProjectDir());

        $command = $application->find('app:reports-summary');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $output = $tester->getDisplay();

        self::assertStringContainsString('Summary of 1 user report(s)', $output);
        self::assertStringContainsString('SMTP is disabled.', $output);
    }

    public function testExecuteWithoutReports(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $tester = new CommandTester($this->createCommand(0, $mailer));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('No user reports found.', $tester->getDisplay());
    }

    public function testExecuteSendsEmail(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->with(self::callback(
            static fn (Email $email): bool => $email->getSubject() === 'Report summary'
                && $email->getTo()[0]->getAddress() === 'admin@domain.com'
                && $email->getTextBody() === 'There are 2 report(s) to review.'
        ));

        $tester = new CommandTester($this->createCommand(2, $mailer));
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Email sent successfully.', $tester->getDisplay());
    }

    public function testExecuteWithMailerFailure(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willThrowException(new TransportException('Connection refused'));

        $tester = new CommandTester($this->createCommand(1, $mailer));
        $status = $tester->execute([]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('Failed to send email: Connection refused', $tester->getDisplay());
    }
}
