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
	 */
	private array $violations;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(array $violations)
	{
		parent::__construct(Response::HTTP_BAD_REQUEST, 'An error occurred during data validation.');

		$this->violations = $violations;
	}

	/**
	 * Récupère les erreurs de validation.
	 */
	public function getViolations(): array
	{
		return $this->violations;
	}
}