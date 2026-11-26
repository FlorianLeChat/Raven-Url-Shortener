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
 * @see https://symfony.com/doc/current/controller/error_pages.html#working-with-the-kernel-exception-event
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class ExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

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

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $exception instanceof HttpException
            ? $this->handleHttpException($exception)
            : $this->handleInternalException($exception);

        $event->setResponse($response);
    }
}
