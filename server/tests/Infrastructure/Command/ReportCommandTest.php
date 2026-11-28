<?php

namespace App\Tests\Infrastructure\Command;

use App\Domain\Entity\Report;
use App\Tests\TestHelperTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Tests of the `app:report` command on the report of ReportFixture, against `test3`.
 */
final class ReportCommandTest extends KernelTestCase
{
    use TestHelperTrait;

    private CommandTester $tester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        self::loadFixtures($kernel->getProjectDir());

        $this->tester = new CommandTester((new Application($kernel))->find('app:report'));
    }

    public function testListReports(): void
    {
        $this->tester->execute(['action' => 'list']);

        $this->tester->assertCommandIsSuccessful();
        self::assertStringContainsString('test3', $this->tester->getDisplay());
        self::assertStringContainsString('johndoe@domain.com', $this->tester->getDisplay());

        $this->tester->execute(['action' => 'list', '--status' => 'accepted']);

        $this->tester->assertCommandIsSuccessful();
        self::assertStringNotContainsString('test3', $this->tester->getDisplay());

        $this->tester->execute(['action' => 'list', '--status' => 'unknown']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('Unknown status "unknown".', $this->tester->getDisplay());
    }

    public function testReviewReport(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $report = $entityManager->getRepository(Report::class)->findOneBy(['email' => 'johndoe@domain.com']);
        $id = (string) $report?->getId();

        $this->tester->execute(['action' => 'review', 'id' => 'not-a-uuid', 'decision' => 'rejected']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('No report has the identifier "not-a-uuid".', $this->tester->getDisplay());

        $this->tester->execute(['action' => 'review', 'id' => $id, 'decision' => 'pending']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('The decision must be accepted or rejected.', $this->tester->getDisplay());

        $this->tester->execute(['action' => 'review', 'id' => $id, 'decision' => 'rejected', '--trusted' => true]);

        $this->tester->assertCommandIsSuccessful();
        self::assertTrue($report?->getLink()->isTrusted());

        $this->tester->execute(['action' => 'review', 'id' => $id, 'decision' => 'accepted']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('already been reviewed', $this->tester->getDisplay());
    }

    public function testUnknownAction(): void
    {
        $this->tester->execute(['action' => 'delete']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('Use one of: list, review.', $this->tester->getDisplay());
    }
}
