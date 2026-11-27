<?php

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * Email address whose domain can receive mail: it is neither local nor reserved, and its DNS
 *  zone publishes an MX record, or an A or AAAA record as fallback, without a "null MX" record.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DeliverableEmail extends Constraint
{
    public const UNDELIVERABLE_ERROR = '4c4d2e7b-5f0a-4d6e-9a43-1f3b8e2c7d90';

    protected const ERROR_NAMES = [
        self::UNDELIVERABLE_ERROR => 'UNDELIVERABLE_ERROR'
    ];

    public string $message = 'email.undeliverable';
}
