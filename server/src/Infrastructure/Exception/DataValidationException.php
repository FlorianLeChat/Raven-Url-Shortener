<?php

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class DataValidationException extends HttpException
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $violations;

    /**
     * @param array<string, array<int, array<string, mixed>>> $violations
     */
    public function __construct(array $violations)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, 'An error occurred during data validation.');

        $this->violations = $violations;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
