<?php

namespace App\Infrastructure\EventListener;

use Throwable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Écouteur d'événements pour la gestion des exceptions.
 * @see https://symfony.com/doc/current/controller/error_pages.html#working-with-the-kernel-exception-event
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class ExceptionListener
{
	/**
	 * Réponse HTTP en format JSON.
	 */
	private JsonResponse $response;

	/**
	 * Événement de l'exception.
	 */
	private ExceptionEvent $event;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger
	) {
		$this->response = new JsonResponse();
	}

	/**
	 * Gestion des exceptions internes.
	 */
	private function handleInternalException(Throwable $exception): void
	{
		$data = [
			'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
			'message' => $exception->getMessage()
		];

		$this->response->setData($data);
		$this->response->setStatusCode(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
	}

	/**
	 * Gestion des exceptions HTTP.
	 */
	private function handleHttpException(HttpExceptionInterface $exception): void
	{
		$data = [
			'code' => $exception->getStatusCode(),
			'message' => $exception->getMessage()
		];

		if ($exception instanceof DataValidationException)
		{
			$data['errors'] = $exception->getViolations();
		}

		$this->response->setData($data);
		$this->response->setStatusCode($exception->getStatusCode());
		$this->response->headers->replace($exception->getHeaders());
		$this->response->headers->set('Content-Type', 'application/json');
	}

	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ExceptionEvent $event): void
	{
		$this->event = $event;

		$exception = $event->getThrowable();

		$this->logger->error($exception->getMessage(), [
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'code' => $exception->getCode(),
			'data' => $exception instanceof DataValidationException ? $exception->getViolations() : []
		]);

		$exception instanceof HttpException
			? $this->handleHttpException($exception)
			: $this->handleInternalException($exception);

		$this->event->setResponse($this->response);
	}
}