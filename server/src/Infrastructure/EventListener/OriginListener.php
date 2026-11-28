<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Adds the CORS headers to every response and, for a private API, restricts its access
 *  to the allowed origins (or the local network) holding the private API key.
 * The restriction runs on `kernel.request` and the headers on `kernel.response` (see `config/services.php`).
 */
final readonly class OriginListener
{
    /**
     * @param string[] $allowedOrigins Allowed origins list for CORS requests.
     */
    public function __construct(
        private bool $isPrivateApi,
        private array $allowedOrigins,
        private string $apiKey,
        private LoggerInterface $logger,
        private TranslatorInterface $translator
    ) {
    }

    /**
     * Tells whether the request comes from the loopback or a private IPv4 range.
     */
    private function isLocalRequest(Request $request): bool
    {
        $ipAddress = $request->getClientIp() ?? '';

        return
            $ipAddress === '127.0.0.1' || $ipAddress === '::1' ||
            preg_match('/^192\.168\./', $ipAddress) ||
            preg_match('/^10\./', $ipAddress) ||
            preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress);
    }

    /**
     * Tells whether the origin is listed, or whether every origin is allowed by `*`.
     */
    private function isAllowedOrigin(string $headerOrigin): bool
    {
        return in_array($headerOrigin, $this->allowedOrigins, true) || $this->allowedOrigins[0] === '*';
    }

    /**
     * @throws AccessDeniedHttpException When the origin is not allowed and the request is not local.
     */
    private function checkRequestOrigin(Request $request, string $headerOrigin): void
    {
        $isValidOrigin = $this->isAllowedOrigin($headerOrigin) || $this->isLocalRequest($request);

        if (!$isValidOrigin) {
            $this->logger->error('HTTP request from unauthorized origin: {origin}', [
                'origin' => $headerOrigin
            ]);

            throw new AccessDeniedHttpException($this->translator->trans('http.invalid_origin'));
        }
    }

    /**
     * Compares the `Authorization: Bearer <key>` header with `APP_PRIVATE_API_KEY`, in constant time.
     * The log only says whether a key was sent: a refused value can be a mistyped real key.
     * @throws AccessDeniedHttpException When the key is missing or does not match.
     */
    private function checkApiKey(Request $request): void
    {
        $headerApiKey = $request->headers->get('Authorization') ?? '';
        $headerApiKey = str_replace('Bearer ', '', $headerApiKey);
        $isValidApiKey = hash_equals($this->apiKey, $headerApiKey);

        if (!$isValidApiKey) {
            $this->logger->error('HTTP request with {apiKey} private API key', [
                'apiKey' => $headerApiKey !== '' ? 'an invalid' : 'no'
            ]);

            throw new AccessDeniedHttpException($this->translator->trans('http.invalid_api_key'));
        }
    }

    /**
     * Allows every origin for a public API, and only the allowed ones for a private API.
     */
    private function handleCorsHeaders(Response $response, string $headerOrigin): void
    {
        if (!$this->isPrivateApi || $this->allowedOrigins[0] === '*') {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        } elseif ($this->isAllowedOrigin($headerOrigin)) {
            $response->headers->set('Access-Control-Allow-Origin', $headerOrigin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    /**
     * Adds the CORS headers, including to the error responses, and answers the preflight `OPTIONS` requests
     *  with a 200 status: no route accepts that method, so the router has answered them with a 405 error.
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $headerOrigin = $request->headers->get('Origin') ?? '';

        if ($headerOrigin !== '') {
            $this->handleCorsHeaders($response, $headerOrigin);
        }

        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(200);
        }
    }

    /**
     * Restricts a private API before the routing and the action run, so a refused request changes nothing.
     * Preflight `OPTIONS` requests carry no credentials and are left to `onKernelResponse()`.
     * An empty `APP_PRIVATE_API_KEY` restricts the private API to its origins, without key check.
     * @throws AccessDeniedHttpException When the origin or the private API key is refused.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $isCheckedRequest = $this->isPrivateApi && $event->isMainRequest() && $request->getMethod() !== 'OPTIONS';

        if ($isCheckedRequest) {
            $this->checkRequestOrigin($request, $request->headers->get('Origin') ?? '');
        }

        if ($isCheckedRequest && $this->apiKey !== '') {
            $this->checkApiKey($request);
        }
    }
}
