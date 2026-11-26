<?php

namespace App\Tests\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ReportFactory;

/**
 * Tests of ReportFactory.
 */
final class ReportFactoryTest extends TestCase
{
    public function testCreateUserReport(): void
    {
        $report = ReportFactory::create($this->createMock('App\Domain\Entity\Link'), 'Spam', 'test@domain.com');

        self::assertSame('test@domain.com', $report->getEmail());
        self::assertSame('Spam', $report->getReason());
        self::assertNotNull($report->getCreatedAt());
        self::assertNull($report->getUpdatedAt());
    }
}
