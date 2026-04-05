<?php

namespace App\Tests\Domain\Factory;

use PHPUnit\Framework\TestCase;
use App\Domain\Factory\ReportFactory;

final class ReportFixtureTest extends TestCase
{
    public function testCreateUserReport(): void
    {
        $report = ReportFactory::create($this->createMock('App\Domain\Entity\Link'), 'Spam', 'test@domain.com');

        $this->assertEquals('test@domain.com', $report->getEmail());
        $this->assertEquals('Spam', $report->getReason());
        $this->assertNotNull($report->getCreatedAt());
        $this->assertNull($report->getUpdatedAt());
    }
}
