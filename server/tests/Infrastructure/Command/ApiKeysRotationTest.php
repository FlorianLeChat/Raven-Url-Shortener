<?php

namespace App\Tests\Infrastructure\Command;

use App\Tests\TestHelperTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Tests of the `app:api-keys-rotation` command on the fixtures.
 */
final class ApiKeysRotationTest extends KernelTestCase
{
    use TestHelperTrait;

    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        self::loadFixtures($kernel->getProjectDir());

        $command = $application->find('app:api-keys-rotation');
        $tester = new CommandTester($command);
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();

        $output = $tester->getDisplay();

        self::assertStringContainsString('Rotating API key', $output);
        self::assertStringContainsString('[OK] Rotated 3 expired API key(s).', $output);
    }
}
