<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Écouteur d'événements pour la gestion des en-têtes HTTP CORS.
 */
final class CorsListener
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		protected readonly TranslatorInterface $translator
	) {}

	/**
	 * Appel de l'écouteur d'événements.
	 */
	public function __invoke(ResponseEvent $event): void
	{
		$response = $event->getResponse();
		$response->headers->set('Access-Control-Allow-Origin', '*');
		$response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
		$response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

		$event->setResponse($response);
	}
}