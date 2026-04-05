<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class OriginListener
{
    private Request $request;
    private Response $response;
    private string $headerOrigin;

    /**
     * @param string[] $allowedOrigins Allowed origins list for CORS requests.
     */
    public function __construct(
        private readonly bool $isPrivateApi,
        private readonly array $allowedOrigins,
        private readonly string $apiKey,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator
    ) {
    }

    private function isLocalRequest(): bool
    {
        $ipAddress = $this->request->getClientIp() ?? '';

        return
            $ipAddress === '127.0.0.1' || $ipAddress === '::1' ||
            preg_match('/^192\.168\./', $ipAddress) ||
            preg_match('/^10\./', $ipAddress) ||
            preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress);
    }

    private function isAllowedOrigin(): bool
    {
        return in_array($this->headerOrigin, $this->allowedOrigins, true) || $this->allowedOrigins[0] === '*';
    }

    private function checkRequestOrigin(): void
    {
        $isValidOrigin = $this->isAllowedOrigin() || $this->isLocalRequest();

        if (!$isValidOrigin) {
            $this->logger->error('HTTP request from unauthorized origin: {origin}', [
                'origin' => $this->headerOrigin
            ]);

            throw new AccessDeniedHttpException($this->translator->trans('http.invalid_origin'));
        }
    }

    private function checkApiKey(): void
    {
        $headerApiKey = $this->request->headers->get('Authorization') ?? '';
        $headerApiKey = str_replace('Bearer ', '', $headerApiKey);
        $isValidApiKey = hash_equals($this->apiKey, $headerApiKey);

        if (!$isValidApiKey) {
            $this->logger->error('HTTP request with invalid API key: {apiKey}', [
                'apiKey' => $headerApiKey !== '' ? $headerApiKey : '(none)'
            ]);

            throw new AccessDeniedHttpException($this->translator->trans('http.invalid_api_key'));
        }
    }

    private function handleCorsHeaders(): void
    {
        if (!$this->isPrivateApi || $this->allowedOrigins[0] === '*') {
            $this->response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif ($this->isAllowedOrigin()) {
            $this->response->headers->set('Access-Control-Allow-Origin', $this->headerOrigin);
        }

        $this->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $this->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    public function __invoke(ResponseEvent $event): void
    {
        $this->request = $event->getRequest();
        $this->response = $event->getResponse();
        $this->headerOrigin = $this->request->headers->get('Origin') ?? '';

        if ($this->headerOrigin !== '') {
            $this->handleCorsHeaders();
        }

        if ($this->request->getMethod() === 'OPTIONS') {
            $this->response->setStatusCode(200);

            $event->setResponse($this->response);
            return;
        }

        if ($this->isPrivateApi) {
            $this->checkRequestOrigin();
            $this->checkApiKey();
        }

        $event->setResponse($this->response);
    }
}
