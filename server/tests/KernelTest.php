<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests of the Kernel settings applied at boot.
 */
final class KernelTest extends KernelTestCase
{
    public function testKernel(): void
    {
        $kernel = self::bootKernel();

        self::assertSame($kernel->getProjectDir() . '/logs', $kernel->getLogDir());
        self::assertSame((string) (getenv('TZ') ?: 'America/New_York'), date_default_timezone_get());
    }
}
