<?php

namespace App\Infrastructure\EventListener;

use Throwable;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use App\Infrastructure\Exception\DataValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Turns every exception into the JSON error body of the API.
 *
 * @see https://symfony.com/doc/current/controller/error_pages.html#working-with-the-kernel-exception-event
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final readonly class ExceptionListener
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * Answers a 500 error for any exception outside HTTP ones, and logs it as critical.
     */
    private function handleInternalException(Throwable $exception): JsonResponse
    {
        $data = [
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $exception->getMessage()
        ];

        $this->logger->critical($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode()
        ]);

        return new JsonResponse($data, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Answers the status and the headers of the HTTP exception, with the violations of a DataValidationException.
     */
    private function handleHttpException(HttpExceptionInterface $exception): JsonResponse
    {
        $data = [
            'code' => $exception->getStatusCode(),
            'message' => $exception->getMessage()
        ];

        if ($exception instanceof DataValidationException) {
            $data['errors'] = $exception->getViolations();
        }

        $this->logger->error($exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'data' => $exception instanceof DataValidationException ? $exception->getViolations() : []
        ]);

        $response = new JsonResponse($data, $exception->getStatusCode(), $exception->getHeaders());
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Replaces the response of the event with the JSON error. A new response is built for each event,
     *  so the headers of one error never leak into the next one in worker mode.
     */
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $exception instanceof HttpException
            ? $this->handleHttpException($exception)
            : $this->handleInternalException($exception);

        $event->setResponse($response);
    }
}
