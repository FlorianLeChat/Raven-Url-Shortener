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
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly TranslatorInterface $translator,
		private readonly ParameterBagInterface $parameterBag
	) {}

	/**
	 * Vérifie si l'origine de la requête est valide.
	 */
	private function checkRequestOrigin(string $origin): void
	{
		$ipAddress = $this->request->getClientIp() ?? '';
		$isLocalAddress =
			// Adresses IPv4/v6 de la boucle locale (localhost)
			$ipAddress === '127.0.0.1' || $ipAddress === '::1' ||
			// Bloc d'adresses IP privées en 192.168.x.x
			preg_match('/^192\.168\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 10.x.x.x
			preg_match('/^10\./', $ipAddress) ||
			// Bloc d'adresses IP privées en 172.16.x.x à 172.31.x.x
			preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ipAddress);

		$isValidOrigin = in_array($origin, $this->allowedOrigins, true);

		if ($isValidOrigin || $isLocalAddress)
		{
			// L'origine est valide ou la requête provient d'une adresse locale.
			return;
		}

		$this->logger->error('HTTP request from unauthorized origin: {origin} (IP: {ip})', [
			'ip' => $ipAddress,
			'origin' => $origin
		]);

		throw new AccessDeniedHttpException($this->translator->trans('http.invalid_origin'));
	}

	/**
	 * Gestion des en-têtes CORS pour les requêtes HTTP.
	 */
	private function handleCorsHeaders(): void
	{
		if ($this->allowedOrigins[0] === '*')
		{
			// Toutes les origines sont autorisées.
			$this->response->headers->set('Access-Control-Allow-Origin', '*');
		}
		else
		{
			// Vérification de l'origine de la requête.
			$origin = $this->request->headers->get('Origin') ?? '';

			$this->checkRequestOrigin($origin);
			$this->response->headers->set('Access-Control-Allow-Origin', $origin);
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
		$this->allowedOrigins = (array) $this->parameterBag->get('app.allowed_origins');

		if ($this->request->getMethod() === 'OPTIONS')
		{
			// Prise en charge des requêtes préliminaires CORS.
			$this->response->setStatusCode(200);
		}

		$this->handleCorsHeaders();

		$event->setResponse($this->response);
	}
}