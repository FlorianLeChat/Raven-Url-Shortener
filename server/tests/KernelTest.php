<?php

namespace App\Tests;

use LogicException;
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

    public function testPrivateApiWithoutCaptchaKey(): void
    {
        $_SERVER['PRIVATE_API'] = $_ENV['PRIVATE_API'] = 'true';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('CAPTCHA_HMAC must be set when the API is private.');

        try {
            self::bootKernel();
        } finally {
            $_SERVER['PRIVATE_API'] = $_ENV['PRIVATE_API'] = 'false';
        }
    }
}
