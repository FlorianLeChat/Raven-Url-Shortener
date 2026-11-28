<?php

namespace App\Domain\Enum;

/**
 * Review state of a report. A pending report locks its link until an administrator accepts or rejects it.
 */
enum ReportStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
