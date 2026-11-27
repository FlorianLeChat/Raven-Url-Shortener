<?php

namespace App\Infrastructure\Validator;

use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\DNSGetRecordWrapper;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Checks the DNS zone of the email address domain for the DeliverableEmail constraint.
 * A failed DNS lookup also rejects the address: it cannot be proven to receive mail.
 */
final class DeliverableEmailValidator extends ConstraintValidator
{
    private const MAX_DOMAIN_LABELS = 5;

    public function __construct(private readonly DNSGetRecordWrapper $dnsResolver)
    {
    }

    /**
     * Adds a violation when the domain cannot receive mail. A null or empty value is left
     *  to the NotBlank constraint.
     * The DNS check queries every parent of an unknown domain, two lookups each: a domain with
     *  more than MAX_DOMAIN_LABELS labels is rejected without any lookup, to bound the time
     *  a request spends waiting for DNS servers.
     * @throws UnexpectedTypeException When the constraint is not a DeliverableEmail.
     * @throws UnexpectedValueException When the value is not a string.
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DeliverableEmail) {
            throw new UnexpectedTypeException($constraint, DeliverableEmail::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $validator = new EmailValidator();
        $domainPart = (string) strrchr($value, '@');
        $isDomainTooDeep = substr_count($domainPart, '.') + 1 > self::MAX_DOMAIN_LABELS;

        if (!$isDomainTooDeep && $validator->isValid($value, new DNSCheckValidation($this->dnsResolver))) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $this->formatValue($value))
            ->setCode(DeliverableEmail::UNDELIVERABLE_ERROR)
            ->addViolation();
    }
}
