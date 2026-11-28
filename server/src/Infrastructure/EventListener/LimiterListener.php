<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use App\Domain\Enum\AccessKeyCapability;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Limits the number of API requests per IP address, with separate limits for reads and writes.
 * Only registered in the production environment (see `config/services.php`).
 *
 * @see https://symfony.com/doc/current/rate_limiter.html#rate-limiting-in-action
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final readonly class LimiterListener
{
    /**
     * @param string[] $enabledLocales Locales the error message can be translated into.
     */
    public function __construct(
        private bool $isRateLimitEnabled,
        private array $enabledLocales,
        private LoggerInterface $logger,
        private RateLimiterFactoryInterface $readApiLimiter,
        private RateLimiterFactoryInterface $writeApiLimiter,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * Picks the write limiter for `POST`, `PUT`, `PATCH` and `DELETE`, the read limiter otherwise.
     */
    private function createLimiterFromRequest(Request $request): RateLimiterFactoryInterface
    {
        $method = $request->getMethod();
        $isWriteMethod = $method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE';

        $this->logger->debug('{method} API request from {ip}', [
            'method' => $isWriteMethod ? 'Write' : 'Read',
            'ip' => IpUtils::anonymize($request->getClientIp() ?? '127.0.0.1')
        ]);

        return $isWriteMethod ? $this->writeApiLimiter : $this->readApiLimiter;
    }

    /**
     * Consumes a token for the client IP address. IP addresses are anonymized in the logs.
     * @throws TooManyRequestsHttpException When the limit is reached, with the `X-RateLimit-*` headers.
     */
    private function consumeLimiter(Request $request, RateLimiterFactoryInterface $limiter): void
    {
        $ipAddress = $request->getClientIp() ?? '127.0.0.1';
        $anonymizedIpAddress = IpUtils::anonymize($ipAddress);

        $limit = $limiter->create($ipAddress)->consume();
        $isRateLimited = !$limit->isAccepted();

        if ($isRateLimited) {
            $locale = $request->getPreferredLanguage($this->enabledLocales);
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $errorMessage = $this->translator->trans('too_many_requests', domain: 'http', locale: $locale);

            $this->logger->warning('Rate limit exceeded for {ip}', ['ip' => $anonymizedIpAddress]);

            throw new TooManyRequestsHttpException($retryAfter, $errorMessage, headers: [
                'X-RateLimit-Reset' => $limit->getRetryAfter()->getTimestamp(),
                'X-RateLimit-Limit' => $limit->getLimit(),
                'X-RateLimit-Remaining' => $limit->getRemainingTokens()
            ]);
        }

        $this->logger->debug('Rate limit for {ip} is {remaining}/{limit}', [
            'ip' => $anonymizedIpAddress,
            'limit' => $limit->getLimit(),
            'remaining' => $limit->getRemainingTokens()
        ]);
    }

    /**
     * Checks the limit of every request when `RATE_LIMIT_ENABLED` is set, except the administrator ones
     *  (see AdminApiKeyListener) and those of an access key granting the `rate_limit_bypass` capability
     *  (see AccessKeyListener).
     */
    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $isAdmin = $request->attributes->getBoolean(AdminApiKeyListener::IS_ADMIN);
        $isBypassed = AccessKeyListener::hasCapability($request, AccessKeyCapability::RateLimitBypass);

        if (!$this->isRateLimitEnabled || $isAdmin || $isBypassed) {
            return;
        }

        $limiter = $this->createLimiterFromRequest($request);

        $this->consumeLimiter($request, $limiter);
    }
}
