<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Écouteur d'événements pour la gestion des origines des requêtes HTTP.
 */
final class OriginListener
{
	/**
	 * Instance de la requête HTTP entrante.
	 */
	private Request $request;

	/**
	 * Instance de la réponse HTTP.
	 */
	private Response $response;

	/**
	 * Liste des origines autorisées pour les requêtes CORS.
	 * @var string[] $allowedOrigins
	 */
	private array $allowedOrigins;

	/**
	 * Origine de la requête HTTP entrante.
	 */
	private string $headerOrigin;

	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly TranslatorInterface $translator,
		private readonly ParameterBagInterface $parameterBag
	) {}

	/**
	 * Détermine si la requête HTTP est d'origine interne.
	 */
	private function isLocalRequest(): bool
	{
		$ipAddress = $this->request->getClientIp() ?? '';

		return
			// Adresses IPv4/v6 de la boucle locale (localhost)
			$ipAddress === '127.0.0.1' || $ipAddress === '::1' ||
			// Bloc d'adresses IP privées en 192.168.x.x
			preg_match('/^192\.168\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 10.x.x.x
			preg_match('/^10\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 172.16.x.x à 172.31.x.x
			preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress);
	}

	/**
	 * Détermine si l'origine de la requête est valide.
	 */
	private function isAllowedOrigin(): bool
	{
		return in_array($this->headerOrigin, $this->allowedOrigins, true) || $this->allowedOrigins[0] === '*';
	}

	/**
	 * Vérifie si l'origine de la requête est valide.
	 */
	private function checkRequestOrigin(): void
	{
		$isValidOrigin = $this->isAllowedOrigin() || $this->isLocalRequest();

		if (!$isValidOrigin)
		{
			$this->logger->error('HTTP request from unauthorized origin: {origin}', [
				'origin' => $this->headerOrigin
			]);

			throw new AccessDeniedHttpException($this->translator->trans('http.invalid_origin'));
		}
	}

	/**
	 * Vérifie si l'origine de la requête est valide.
	 */
	private function checkApiKey(): void
	{
		$headerApiKey = $this->request->headers->get('Authorization') ?? '';
		$headerApiKey = str_replace('Bearer ', '', $headerApiKey);
		$isValidApiKey = hash_equals($this->parameterBag->get('api.key'), $headerApiKey);

		if (!$isValidApiKey)
		{
			$this->logger->error('HTTP request with invalid API key: {apiKey}', [
				'apiKey' => $headerApiKey !== '' ? $headerApiKey : '(none)'
			]);

			throw new AccessDeniedHttpException($this->translator->trans('http.invalid_api_key'));
		}
	}

	/**
	 * Gestion des en-têtes CORS pour les requêtes HTTP.
	 */
	private function handleCorsHeaders(): void
	{
		if (!$this->parameterBag->get('api.private') || $this->allowedOrigins[0] === '*')
		{
			// L'API est publique ou toutes les origines sont autorisées.
			// Une API peut autoriser toutes les origines tout en demandant une clé API valide.
			$this->response->headers->set('Access-Control-Allow-Origin', '*');
		}
		elseif ($this->isAllowedOrigin())
		{
			$this->response->headers->set('Access-Control-Allow-Origin', $this->headerOrigin);
		}

		$this->response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
		$this->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
	}

	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ResponseEvent $event): void
	{
		$this->request = $event->getRequest();
		$this->response = $event->getResponse();

		$this->headerOrigin = $this->request->headers->get('Origin') ?? '';
		$this->allowedOrigins = (array) $this->parameterBag->get('api.allowed_origins');

		if ($this->headerOrigin !== '')
		{
			// Gestion des en-têtes CORS si l'en-tête « Origin » est présent.
			$this->handleCorsHeaders();
		}

		if ($this->request->getMethod() === 'OPTIONS')
		{
			// Prise en charge des requêtes préliminaires CORS.
			$this->response->setStatusCode(200);

			// Pas besoin de vérifier la clé API pour une requête OPTIONS
			//  même si l'API est privée.
			$event->setResponse($this->response);
			return;
		}

		if ($this->parameterBag->get('api.private'))
		{
			// Pour une API privée, il faut vérifier l'origine de la requête
			//  et vérifier si la clé API fournie est valide.
			$this->checkRequestOrigin();
			$this->checkApiKey();
		}

		$event->setResponse($this->response);
	}
}