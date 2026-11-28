<?php

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * 400 error raised when the data received by the API breaks a constraint.
 * Its violations are added to the JSON error body by ExceptionListener.
 */
final class DataValidationException extends HttpException
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $violations;

    /**
     * @param array<string, array<int, array<string, mixed>>> $violations Violations by field,
     *  each one with its `code`, the rejected `value` and its `message`.
     */
    public function __construct(array $violations)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, 'An error occurred during data validation.');

        $this->violations = $violations;
    }

    /**
     * Throws the violations found by the Symfony validator, grouped by property, when there is at least one.
     * @throws self
     */
    public static function throwOnViolations(ConstraintViolationListInterface $violations): void
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()][] = [
                'code' => $violation->getConstraint()?->getErrorName($violation->getCode() ?? '') ?? '',
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage()
            ];
        }

        if (!empty($errors)) {
            throw new self($errors);
        }
    }

    /**
     * Violations by field, as given to the constructor.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
