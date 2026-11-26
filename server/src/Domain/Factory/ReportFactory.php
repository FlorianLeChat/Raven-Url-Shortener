<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Link;
use App\Domain\Entity\Report;

/**
 * Creates the reports filed by visitors.
 */
final class ReportFactory
{
    /**
     * Creates a report on the link, before its validation.
     */
    public static function create(Link $link, string $reason, ?string $email = null): Report
    {
        $report = new Report();
        $report->setLink($link);
        $report->setEmail($email);
        $report->setReason($reason);

        return $report;
    }
}
