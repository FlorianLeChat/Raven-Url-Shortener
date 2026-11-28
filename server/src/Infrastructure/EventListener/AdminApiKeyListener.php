<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Recognizes the administrator API key (`ADMIN_API_KEY`) in the `Authorization: Bearer <key>` header
 *  and marks the request with the `IS_ADMIN` attribute, read by the other checks instead of the key:
 *  the administrator skips the private API key, the rate limiter and the CAPTCHA, reads any link without
 *  its password, manages any link without its API key, and reviews the reports.
 * It runs on `kernel.request` before the rate limiter (see `config/services.php`).
 */
final readonly class AdminApiKeyListener
{
    public const IS_ADMIN = '_admin';

    /**
     * @param string $adminApiKey The administrator API key. Empty, no request is an administrator one.
     */
    public function __construct(private string $adminApiKey)
    {
    }

    /**
     * Compares the bearer token with the administrator API key, in constant time.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $headerApiKey = str_replace('Bearer ', '', $request->headers->get('Authorization') ?? '');
        $isAdmin = $this->adminApiKey !== '' && hash_equals($this->adminApiKey, $headerApiKey);

        $request->attributes->set(self::IS_ADMIN, $isAdmin);
    }
}
