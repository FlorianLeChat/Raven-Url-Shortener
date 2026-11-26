<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * @see https://symfony.com/doc/current/rate_limiter.html#rate-limiting-in-action
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class LimiterListener
{
    /**
     * @param string[] $enabledLocales Langues disponibles pour traduire le message d'erreur.
     */
    public function __construct(
        private readonly bool $isRateLimitEnabled,
        private readonly array $enabledLocales,
        private readonly LoggerInterface $logger,
        private readonly RateLimiterFactoryInterface $readApiLimiter,
        private readonly RateLimiterFactoryInterface $writeApiLimiter,
        protected readonly TranslatorInterface $translator
    ) {
    }

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

    private function consumeLimiter(Request $request, RateLimiterFactoryInterface $limiter): void
    {
        $ipAddress = $request->getClientIp() ?? '127.0.0.1';
        $anonymizedIpAddress = IpUtils::anonymize($ipAddress);

        $limit = $limiter->create($ipAddress)->consume();
        $isRateLimited = !$limit->isAccepted();

        if ($isRateLimited) {
            $locale = $request->getPreferredLanguage($this->enabledLocales);
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $errorMessage = $this->translator->trans('http.too_many_requests', locale: $locale);

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

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->isRateLimitEnabled) {
            return;
        }

        $request = $event->getRequest();
        $limiter = $this->createLimiterFromRequest($request);

        $this->consumeLimiter($request, $limiter);
    }
}
