<?php

namespace App\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Action pour la page d'accueil du serveur.
 */
final class ServerIndexAction extends AbstractController
{
	/**
	 * Récupération de l'état de santé du serveur.
	 */
	#[Route("/", methods: ["GET"], stateless: true)]
	public function getHealthCheck(Request $request): Response
	{
		$requestTime = $request->server->get("REQUEST_TIME_FLOAT");
		$currentTime = microtime(true);
		$executionTime = round(($currentTime - $requestTime) * 1000, 3);

		return new Response("OK in $executionTime ms");
	}
}