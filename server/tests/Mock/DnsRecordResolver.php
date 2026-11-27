<?php

namespace App\Tests\Mock;

use Egulias\EmailValidator\Validation\DNSRecords;
use Egulias\EmailValidator\Validation\DNSGetRecordWrapper;

/**
 * Mocked DNS lookups of the test environment: they replace the checks of the email address
 *  domains, so the tests never reach the network.
 */
final class DnsRecordResolver extends DNSGetRecordWrapper
{
    public const NO_RECORD_HOST = 'no-record.com';

    /**
     * Answers no record for the host of the scenario, and an MX record for any other host.
     */
    public function getRecords(string $host, int $type): DNSRecords
    {
        if ($host === self::NO_RECORD_HOST) {
            return new DNSRecords([]);
        }

        return new DNSRecords([['host' => $host, 'type' => 'MX', 'target' => 'mx.' . $host]]);
    }
}
