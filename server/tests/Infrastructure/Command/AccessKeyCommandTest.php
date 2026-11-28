<?php

namespace App\Tests\Infrastructure\Command;

use App\Tests\TestHelperTrait;
use App\Domain\Entity\AccessKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Tests of the `app:access-key` command on the fixtures.
 */
final class AccessKeyCommandTest extends KernelTestCase
{
    use TestHelperTrait;

    private CommandTester $tester;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        self::loadFixtures($kernel->getProjectDir());

        $this->tester = new CommandTester((new Application($kernel))->find('app:access-key'));
    }

    public function testCreateAccessKey(): void
    {
        $this->tester->execute([
            'action' => 'create',
            'target' => 'Partner',
            '--capability' => ['rate_limit_bypass', 'private_api'],
            '--origin' => ['https://partner.example.com'],
            '--expiration' => '+1 month'
        ]);

        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();

        self::assertStringContainsString('Access key "Partner" created', $output);
        self::assertMatchesRegularExpression('/^[A-Za-z0-9+\/]{43}=\r?$/m', $output);

        $this->tester->execute(['action' => 'create', 'target' => 'Partner', '--capability' => ['admin']]);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString(
            'capabilities: One or more of the given values is invalid.',
            $this->tester->getDisplay()
        );
    }

    public function testListAccessKeys(): void
    {
        $this->tester->execute(['action' => 'list']);

        $this->tester->assertCommandIsSuccessful();

        $output = $this->tester->getDisplay();

        self::assertStringContainsString('https://full.example.com', $output);
        self::assertStringContainsString('never', $output);
        self::assertStringNotContainsString('ysvT1q67SCh7h', $output);
    }

    public function testRevokeAccessKey(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $fullKey = $entityManager->getRepository(AccessKey::class)->findOneBy(['name' => 'full']);
        $id = (string) $fullKey?->getId();

        $this->tester->execute(['action' => 'revoke', 'target' => $id]);

        $this->tester->assertCommandIsSuccessful();

        $this->tester->execute(['action' => 'revoke', 'target' => $id]);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('already been revoked', $this->tester->getDisplay());

        $this->tester->execute(['action' => 'revoke', 'target' => 'not-a-uuid']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('No access key has the identifier "not-a-uuid".', $this->tester->getDisplay());
    }

    public function testUnknownAction(): void
    {
        $this->tester->execute(['action' => 'delete']);

        self::assertSame(Command::FAILURE, $this->tester->getStatusCode());
        self::assertStringContainsString('Use one of: create, list, revoke.', $this->tester->getDisplay());
    }
}
