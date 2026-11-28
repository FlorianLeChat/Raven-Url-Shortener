<?php

namespace App\Domain\Factory;

use DateTimeImmutable;
use App\Domain\Entity\Link;
use App\Domain\Entity\Report;
use App\Domain\Enum\ReportStatus;

/**
 * Creates the reports filed by visitors and records the decision of the administrator reviewing them.
 */
final class ReportFactory
{
    /**
     * Creates a pending report on the link, before its validation.
     */
    public static function create(Link $link, string $reason, ?string $email = null, string $locale = 'en'): Report
    {
        $report = new Report();
        $report->setLink($link);
        $report->setEmail($email);
        $report->setReason($reason);
        $report->setLocale($locale);

        return $report;
    }

    /**
     * Closes the report with the decision of the administrator and applies it to the link:
     *  an accepted report disables the link, a rejected one can mark it as trusted so it cannot be reported again.
     */
    public static function review(Report $report, ReportStatus $status, bool $trusted = false): Report
    {
        $now = new DateTimeImmutable();
        $link = $report->getLink();

        $report->setStatus($status);
        $report->setReviewedAt($now);
        $report->setUpdatedAt($now);

        if ($status === ReportStatus::Accepted) {
            $link->setEnabled(false);
        }

        if ($status === ReportStatus::Rejected && $trusted) {
            $link->setTrusted(true);
        }

        return $report;
    }
}
