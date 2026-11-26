<?php

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * Violations by field, as given to the constructor.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
