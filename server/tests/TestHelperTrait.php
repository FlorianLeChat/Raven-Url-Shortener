<?php

namespace App\Tests;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Helpers shared by the tests.
 */
trait TestHelperTrait
{
    /**
     * Reloads the fixtures in the test database, in a child process.
     */
    protected static function loadFixtures(string $projectDir): void
    {
        $php = new PhpExecutableFinder();
        $process = new Process([
            $php->find(),
            sprintf('%s/bin/console', $projectDir),
            'doctrine:fixtures:load',
            '--env=test',
            '--no-interaction'
        ]);

        $process->disableOutput();
        $process->mustRun();
    }
}
