<?php

namespace App\Tests\Domain\Command;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

final class OutdatedShortcutCleanupTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $php = new PhpExecutableFinder();
        $process = new Process([
            $php->find(),
            sprintf('%s/bin/console', $kernel->getProjectDir()),
            'doctrine:fixtures:load',
            '--env=test',
            '--no-interaction'
        ]);

        $process->disableOutput();
        $process->run();

        $command = $application->find('app:shortcut-cleanup');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $output = $tester->getDisplay();

        $this->assertStringContainsString('[OK] Deleted 1 expired shortcut link(s).', $output);
    }
}
