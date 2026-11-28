<?php

namespace App\Tests\Infrastructure\Validator;

use App\Tests\Mock\DnsRecordResolver;
use Symfony\Component\Validator\Constraints\Email;
use App\Infrastructure\Validator\DeliverableEmail;
use App\Infrastructure\Validator\DeliverableEmailValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Tests of the DeliverableEmail constraint, called without the API: the values no request
 *  can send, and the domains rejected before any DNS lookup.
 *
 * @extends ConstraintValidatorTestCase<DeliverableEmailValidator>
 */
final class DeliverableEmailValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): DeliverableEmailValidator
    {
        return new DeliverableEmailValidator(new DnsRecordResolver());
    }

    public function testEmptyString(): void
    {
        $this->validator->validate('', new DeliverableEmail());

        $this->assertNoViolation();
    }

    public function testTooDeepDomain(): void
    {
        $this->validator->validate('johndoe@a.b.c.d.domain.com', new DeliverableEmail());

        $this->buildViolation('email.undeliverable')
            ->setParameter('{{ value }}', '"johndoe@a.b.c.d.domain.com"')
            ->setCode(DeliverableEmail::UNDELIVERABLE_ERROR)
            ->assertRaised();
    }

    public function testWrongConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('johndoe@domain.com', new Email());
    }

    public function testNonStringValue(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(42, new DeliverableEmail());
    }
}
