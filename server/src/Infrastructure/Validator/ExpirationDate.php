<?php

namespace App\Infrastructure\Validator;

use Exception;
use DateTimeImmutable;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Infrastructure\Exception\DataValidationException;

/**
 * Parses the expiration dates received by the API, those of the links and of the access keys,
 *  so an unparsable date answers a validation error rather than a server error.
 */
final class ExpirationDate
{
    /**
     * Parses a date in any format understood by DateTimeImmutable, in the default time zone.
     * An empty value means no expiration date. The message reuses the translation of the Symfony `Date` constraint.
     * @throws DataValidationException When DateTimeImmutable does not understand the date, on the `expiresAt` field.
     */
    public static function parse(string $expiration, TranslatorInterface $translator): ?DateTimeImmutable
    {
        if ($expiration === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($expiration);
        } catch (Exception) {
            $errors = [];
            $errors['expiresAt'][] = [
                'code' => 'INVALID_DATE_ERROR',
                'value' => $expiration,
                'message' => $translator->trans('This value is not a valid date.', domain: 'validators')
            ];

            throw new DataValidationException($errors);
        }
    }
}
