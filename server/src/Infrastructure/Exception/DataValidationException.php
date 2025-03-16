<?php

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Exception personnalisée pour les erreurs de validation des données.
 */
final class DataValidationException extends HttpException
{
	/**
	 * Liste des erreurs de validation.
	 * @var array<string, array<int, array<string, string>>>
	 */
	private array $violations;

	/**
	 * Constructeur de la classe.
	 * @param array<string, array<int, array<string, string>>> $violations
	 */
	public function __construct(array $violations)
	{
		parent::__construct(Response::HTTP_BAD_REQUEST, 'An error occurred during data validation.');

		$this->violations = $violations;
	}

	/**
	 * Récupère les erreurs de validation.
	 * @return array<string, array<int, array<string, string>>>
	 */
	public function getViolations(): array
	{
		return $this->violations;
	}
}