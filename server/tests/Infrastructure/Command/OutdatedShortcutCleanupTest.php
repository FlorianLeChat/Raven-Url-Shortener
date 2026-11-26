<?php

namespace App\Tests\Infrastructure\Command;

use App\Tests\TestHelperTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Tests of the `app:shortcut-cleanup` command on the fixtures.
 */
final class OutdatedShortcutCleanupTest extends KernelTestCase
{
    use TestHelperTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        self::loadFixtures($kernel->getProjectDir());

        $command = $application->find('app:shortcut-cleanup');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $output = $tester->getDisplay();

        self::assertStringContainsString('Deleting shortcut', $output);
        self::assertStringContainsString('[OK] Deleted 1 expired shortcut link(s).', $output);
    }
}
