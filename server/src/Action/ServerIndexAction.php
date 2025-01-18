<?php

namespace App\Action;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use const App\LOG_FUNCTION;

/**
 * Action pour la page d'accueil du serveur.
 */
final class ServerIndexAction extends AbstractController
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(
		public readonly LoggerInterface $logger,
	) {}

	/**
	 * Récupération de l'état de santé du serveur.
	 */
	#[Route("/")]
	public function getHealthCheck(Request $request): Response
	{
		$this->logger->info(sprintf(LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

		$requestTime = $request->server->get("REQUEST_TIME_FLOAT");
		$currentTime = microtime(true);
		$executionTime = round(($currentTime - $requestTime) * 1000, 3);

		return new Response("OK in $executionTime ms");
	}
}