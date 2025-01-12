<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Écouteur d'événements pour la gestion des exceptions.
 * @see https://symfony.com/doc/current/controller/error_pages.html#working-with-the-kernel-exception-event
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class ExceptionListener
{
	/**
	 * Instanciation de la classe.
	 */
	public function __invoke(ExceptionEvent $event): void
	{
		// Génération de la réponse JSON.
		$exception = $event->getThrowable();
		$response = new JsonResponse([
			"code" => $exception->getCode(),
			"message" => $exception->getMessage()
		]);

		if ($exception instanceof HttpExceptionInterface)
		{
			// Exception HTTP.
			$response->setStatusCode($exception->getStatusCode());
			$response->headers->replace($exception->getHeaders());
			$response->headers->set("Content-Type", "application/json");
		}
		else
		{
			// Exception non gérée.
			$response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
		}

		$event->setResponse($response);
	}
}