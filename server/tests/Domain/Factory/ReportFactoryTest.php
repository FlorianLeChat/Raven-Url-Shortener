<?php

namespace App\Tests\Domain\Factory;

use App\Domain\Entity\Link;
use PHPUnit\Framework\TestCase;
use App\Domain\Enum\ReportStatus;
use App\Domain\Factory\ReportFactory;

/**
 * Tests of ReportFactory.
 */
final class ReportFactoryTest extends TestCase
{
    public function testCreateUserReport(): void
    {
        $report = ReportFactory::create($this->createMock(Link::class), 'Spam', 'test@domain.com', 'fr');

        self::assertSame('test@domain.com', $report->getEmail());
        self::assertSame('Spam', $report->getReason());
        self::assertSame('fr', $report->getLocale());
        self::assertSame(ReportStatus::Pending, $report->getStatus());
        self::assertNotNull($report->getCreatedAt());
        self::assertNull($report->getUpdatedAt());
    }

    public function testReviewAcceptedReportDisablesLink(): void
    {
        $link = new Link();
        $report = ReportFactory::review(ReportFactory::create($link, 'Spam'), ReportStatus::Accepted, true);

        self::assertSame(ReportStatus::Accepted, $report->getStatus());
        self::assertNotNull($report->getReviewedAt());
        self::assertSame($report->getReviewedAt(), $report->getUpdatedAt());
        self::assertFalse($link->isEnabled());
        self::assertFalse($link->isTrusted());
    }

    public function testReviewRejectedReportCanTrustLink(): void
    {
        $link = new Link();
        $report = ReportFactory::review(ReportFactory::create($link, 'Spam'), ReportStatus::Rejected, true);

        self::assertSame(ReportStatus::Rejected, $report->getStatus());
        self::assertTrue($link->isEnabled());
        self::assertTrue($link->isTrusted());
    }
}
