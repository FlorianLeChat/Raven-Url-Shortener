<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Écouteur d'événements pour la gestion des en-têtes HTTP CORS.
 */
final class CorsListener
{
	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ResponseEvent $event): void
	{
		$response = $event->getResponse();

		if ($event->getRequest()->getMethod() === 'OPTIONS') {
			// Prise en charge des requêtes préliminaires CORS.
			$response->setStatusCode(200);
		}

		$response->headers->set('Access-Control-Allow-Origin', '*');
		$response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
		$response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

		$event->setResponse($response);
	}
}