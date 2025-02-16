<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Écouteur d'événements pour la gestion des exceptions.
 * @see https://symfony.com/doc/current/controller/error_pages.html#working-with-the-kernel-exception-event
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class ExceptionListener
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger
	) {}

	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ExceptionEvent $event): void
	{
		// Génération de la réponse JSON.
		$response = new JsonResponse();
		$exception = $event->getThrowable();

		$this->logger->error($response->getContent(), [
			"file" => $exception->getFile(),
			"line" => $exception->getLine(),
			"code" => $exception->getCode()
		]);

		if ($exception instanceof HttpException)
		{
			// Exception HTTP standard ou dérivée.
			$data = [
				"code" => $exception->getStatusCode(),
				"message" => $exception->getMessage()
			];

			if ($exception instanceof DataValidationException)
			{
				$data["errors"] = $exception->getViolations();
			}

			$response->setData($data);
			$response->setStatusCode($exception->getStatusCode());
			$response->headers->replace($exception->getHeaders());
			$response->headers->set("Content-Type", "application/json");
		}
		else
		{
			// Exception non gérée.
			$response->setData([
				"code" => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
				"message" => $exception->getMessage()
			]);

			$response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
		}

		$event->setResponse($response);
	}
}