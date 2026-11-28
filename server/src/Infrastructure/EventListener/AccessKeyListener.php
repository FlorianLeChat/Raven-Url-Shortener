<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Psr\Cache\InvalidArgumentException;
use App\Domain\Enum\AccessKeyCapability;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Security\AccessKeyCache;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Recognizes an active access key in the `X-Api-Key` header and marks the request with its capabilities
 *  in the `CAPABILITIES` attribute, read by the checks they relax: OriginListener (private API), LimiterListener,
 *  CaptchaVerifier and the link services (trusted links). A key granting `private_api` also gives its browser
 *  origins in the `ALLOWED_ORIGINS` attribute, read by OriginListener.
 * A preflight request never carries the key: on a private API, its origin goes in `ALLOWED_ORIGINS` when
 *  an active key granting `private_api` declares it, so the browser may send the request carrying the key.
 * The private API key (`PRIVATE_API_KEY`) is left to OriginListener without any lookup: the front-end sends it
 *  with every request. The other keys are read through AccessKeyCache. An unknown, expired or revoked key sets
 *  no attribute: OriginListener refuses it once the rate limiter has counted the request, so guessing keys
 *  stays limited.
 * It runs on `kernel.request` before the rate limiter (see `config/services.php`).
 */
final readonly class AccessKeyListener
{
    public const CAPABILITIES = '_access_key_capabilities';
    public const ALLOWED_ORIGINS = '_access_key_origins';
    public const API_KEY_HEADER = 'X-Api-Key';

    /**
     * @param string $privateApiKey The private API key, compared in constant time before any lookup.
     */
    public function __construct(
        private bool $isPrivateApi,
        private string $privateApiKey,
        private LoggerInterface $logger,
        private AccessKeyCache $accessKeyCache
    ) {
    }

    /**
     * Tells whether the request carries an active access key granting the capability.
     */
    public static function hasCapability(Request $request, AccessKeyCapability $capability): bool
    {
        $capabilities = $request->attributes->get(self::CAPABILITIES, []);

        return is_array($capabilities) && in_array($capability->value, $capabilities, true);
    }

    /**
     * Browser origins the access key of the request allows on a private API.
     *
     * @return string[]
     */
    public static function getAllowedOrigins(Request $request): array
    {
        $origins = $request->attributes->get(self::ALLOWED_ORIGINS, []);

        return is_array($origins) ? array_filter($origins, 'is_string') : [];
    }

    /**
     * Allows the origin of a preflight request on a private API when an active key declares it.
     * @throws InvalidArgumentException When the cache refuses the key, which never happens with a constant.
     */
    private function allowDeclaredOrigin(Request $request): void
    {
        $headerOrigin = $request->headers->get('Origin') ?? '';
        $isCheckedOrigin = $this->isPrivateApi && $headerOrigin !== '';

        if ($isCheckedOrigin && $this->accessKeyCache->isDeclaredOrigin($headerOrigin)) {
            $request->attributes->set(self::ALLOWED_ORIGINS, [$headerOrigin]);
        }
    }

    /**
     * Looks the key up and records the capabilities of an active one, or checks the origin of a preflight request.
     * @throws InvalidArgumentException When the cache refuses the key, which never happens with a hash.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $headerApiKey = $request->headers->get(self::API_KEY_HEADER) ?? '';
        $isPrivateApiKey = $this->privateApiKey !== '' && hash_equals($this->privateApiKey, $headerApiKey);

        if ($event->isMainRequest() && $request->getMethod() === 'OPTIONS') {
            $this->allowDeclaredOrigin($request);
        }

        if (!$event->isMainRequest() || $headerApiKey === '' || $isPrivateApiKey) {
            return;
        }

        $accessKey = $this->accessKeyCache->findActive($headerApiKey);

        if ($accessKey === null) {
            return;
        }

        $this->logger->info('HTTP request with the access key {id}', ['id' => $accessKey->id]);

        $request->attributes->set(self::CAPABILITIES, $accessKey->capabilities);

        if (in_array(AccessKeyCapability::PrivateApi->value, $accessKey->capabilities, true)) {
            $request->attributes->set(self::ALLOWED_ORIGINS, $accessKey->allowedOrigins);
        }
    }
}
