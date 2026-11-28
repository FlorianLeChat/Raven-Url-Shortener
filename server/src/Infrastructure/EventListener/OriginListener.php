<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Adds the CORS headers to every response and, for a private API, restricts its access
 *  to the allowed origins (or the local network) sending the private API key in the `X-Api-Key` header.
 * The `Authorization` header carries the secrets of the routes: the API key or the password of a link,
 *  or the administrator API key. A route reading it declares its secret in the `CREDENTIALS_ROUTE` default;
 *  any other route refuses it, except with the administrator API key, which also skips the private API key
 *  (see AdminApiKeyListener). An API reading no private API key refuses the `X-Api-Key` header.
 * The checks run on `kernel.request`, after the routing, and the headers on `kernel.response`
 *  (see `config/services.php`).
 */
final readonly class OriginListener
{
    public const CREDENTIALS_ROUTE = '_credentials';
    public const LINK_API_KEY = 'link_api_key';
    public const LINK_PASSWORD = 'link_password';
    public const ADMIN_API_KEY = 'admin_api_key';

    private const API_KEY_HEADER = 'X-Api-Key';

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
            preg_match('/^172\.(1[6-9]|2\d|3[0-1])\./', $ipAddress);
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

            throw new AccessDeniedHttpException($this->translator->trans('invalid_origin', domain: 'http'));
        }
    }

    /**
     * Compares the `X-Api-Key` header with `PRIVATE_API_KEY`, in constant time.
     * The log only says whether a key was sent: a refused value can be a mistyped real key.
     * @throws AccessDeniedHttpException When the key is missing or does not match.
     */
    private function checkApiKey(Request $request): void
    {
        $headerApiKey = $request->headers->get(self::API_KEY_HEADER) ?? '';
        $isValidApiKey = hash_equals($this->apiKey, $headerApiKey);

        if (!$isValidApiKey) {
            $this->logger->error('HTTP request with {apiKey} private API key', [
                'apiKey' => $headerApiKey !== '' ? 'an invalid' : 'no'
            ]);

            throw new AccessDeniedHttpException($this->translator->trans('invalid_api_key', domain: 'http'));
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
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Api-Key');
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
     * Refuses the headers no check reads: the `X-Api-Key` header when the API reads no private API key,
     *  and the `Authorization` header on a route without secret, unless it carries the administrator API key.
     * @throws BadRequestHttpException When the request sends one of those headers.
     */
    private function checkUnreadHeaders(Request $request, bool $isApiKeyRead, bool $isAdmin): void
    {
        $hasCredentialsRoute = $request->attributes->has(self::CREDENTIALS_ROUTE);
        $hasApiKey = $request->headers->has(self::API_KEY_HEADER);
        $hasAuthorization = ($request->headers->get('Authorization') ?? '') !== '';

        if (!$isApiKeyRead && $hasApiKey) {
            throw new BadRequestHttpException($this->translator->trans('unexpected_api_key', domain: 'http'));
        }

        if (!$hasCredentialsRoute && !$isAdmin && $hasAuthorization) {
            throw new BadRequestHttpException($this->translator->trans('unexpected_authorization', domain: 'http'));
        }
    }

    /**
     * Restricts a private API before the action runs, so a refused request changes nothing.
     * Preflight `OPTIONS` requests carry no credentials and are left to `onKernelResponse()`.
     * An empty `PRIVATE_API_KEY` restricts the private API to its origins, without key check.
     * @throws AccessDeniedHttpException When the origin or the private API key is refused.
     * @throws BadRequestHttpException When the request sends a header no check reads.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || $request->getMethod() === 'OPTIONS') {
            return;
        }

        if ($this->isPrivateApi) {
            $this->checkRequestOrigin($request, $request->headers->get('Origin') ?? '');
        }

        $isAdmin = $request->attributes->getBoolean(AdminApiKeyListener::IS_ADMIN);
        $isApiKeyRead = $this->isPrivateApi && $this->apiKey !== '';

        $this->checkUnreadHeaders($request, $isApiKeyRead, $isAdmin);

        if ($isApiKeyRead && !$isAdmin) {
            $this->checkApiKey($request);
        }
    }
}
