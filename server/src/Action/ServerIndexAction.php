<?php

namespace App\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Action pour la page d'accueil du serveur.
 */
final class ServerIndexAction
{
	/**
	 * Récupération de l'état de santé du serveur.
	 */
	#[Route("/")]
	public function getHealthCheck(Request $request): Response
	{
		$requestTime = $request->server->get("REQUEST_TIME_FLOAT");
		$currentTime = microtime(true);
		$executionTime = round(($currentTime - $requestTime) * 1000, 3);

		return new Response("OK in $executionTime ms");
	}
}