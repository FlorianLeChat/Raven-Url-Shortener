<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Écouteur d'événements pour la gestion de la limitation de requêtes.
 * @see https://symfony.com/doc/current/rate_limiter.html#rate-limiting-in-action
 * @see https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
 */
final class LimiterListener
{
	/**
	 * Instance de la requête HTTP entrante.
	 */
	private Request $request;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly RateLimiterFactory $readApiLimiter,
		private readonly RateLimiterFactory $writeApiLimiter,
		private readonly ParameterBagInterface $parameterBag,
		protected readonly TranslatorInterface $translator
	) {}

	/**
	 * Création du limiteur de requêtes en fonction de la méthode HTTP.
	 */
	private function createLimiterFromRequest(): RateLimiterFactory
	{
		$method = $this->request->getMethod();
		$isWriteMethod = $method === 'POST' || $method === 'PUT' || $method === 'PATCH' || $method === 'DELETE';

		$this->logger->debug('{method} API request from {ip}', [
			'method' => $isWriteMethod ? 'Write' : 'Read',
			'ip' => IpUtils::anonymize($this->request->getClientIp() ?? '127.0.0.1')
		]);

		return $isWriteMethod ? $this->writeApiLimiter : $this->readApiLimiter;
	}

	/**
	 * Consommation du limiteur de requêtes.
	 */
	private function consumeLimiter(RateLimiterFactory $limiter): void
	{
		$ipAddress = $this->request->getClientIp() ?? '127.0.0.1';
		$anonymizedIpAddress = IpUtils::anonymize($ipAddress);

		$limit = $limiter->create($ipAddress)->consume();
		$isRateLimited = !$limit->isAccepted();

		if ($isRateLimited)
		{
			$retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
			$errorMessage = $this->translator->trans('http.too_many_requests');

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
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(RequestEvent $event): void
	{
		if (!$this->parameterBag->get('rate_limit.enabled'))
		{
			return;
		}

		$this->request = $event->getRequest();

		$limiter = $this->createLimiterFromRequest();

		$this->consumeLimiter($limiter);
	}
}