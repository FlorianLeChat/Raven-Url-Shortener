<?php

namespace App\Infrastructure\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Écouteur d'événements pour la gestion des en-têtes HTTP CORS.
 */
final class CorsListener
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ParameterBagInterface $parameterBag
	) {}

	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ResponseEvent $event): void
	{
		$response = $event->getResponse();
		$request = $event->getRequest();
		$origin = $request->headers->get('Origin') ?? '';

		/** @var string[] $allowedOrigins */
		$allowedOrigins = $this->parameterBag->get('app.allowed_origins');
		$isValidOrigin = in_array($origin, $allowedOrigins, true);

		if ($request->getMethod() === 'OPTIONS')
		{
			// Prise en charge des requêtes préliminaires CORS.
			$response->setStatusCode(200);
		}

		if ($allowedOrigins[0] === '*')
		{
			// Toutes les origines sont autorisées.
			$response->headers->set('Access-Control-Allow-Origin', '*');
		}
		elseif ($isValidOrigin)
		{
			// L'origine de la requête est autorisée.
			$response->headers->set('Access-Control-Allow-Origin', $origin);
		}
		else
		{
			$this->logger->error('CORS request from unauthorized origin: {origin}', ['origin' => $origin]);
		}

		$response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
		$response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

		$event->setResponse($response);
	}
}