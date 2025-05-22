<?php

namespace App\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

const ASCII_INDEX = <<<EOT
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: ____                                     _   _          _     ____    _                      _                                 ::
::|  _ \    __ _  __   __   ___   _ __     | | | |  _ __  | |   / ___|  | |__     ___    _ __  | |_    ___   _ __     ___   _ __  ::
::| |_) |  / _` | \ \ / /  / _ \ | '_ \    | | | | | '__| | |   \___ \  | '_ \   / _ \  | '__| | __|  / _ \ | '_ \   / _ \ | '__| ::
::|  _ <  | (_| |  \ V /  |  __/ | | | |   | |_| | | |    | |    ___) | | | | | | (_) | | |    | |_  |  __/ | | | | |  __/ | |    ::
::|_| \_\  \__,_|   \_/    \___| |_| |_|    \___/  |_|    |_|   |____/  |_| |_|  \___/  |_|     \__|  \___| |_| |_|  \___| |_|    ::
::                                                                                                                                ::
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

Server is running. Response time: %s ms.
Ready to handle requests.
API documentation available at https://url.florian-dev.fr/api/docs.
EOT;

/**
 * Action pour la page d'accueil du serveur.
 */
final class ServerIndexAction extends AbstractController
{
	/**
	 * Récupération de l'état de santé du serveur.
	 */
	#[Route('/', methods: ['GET'], stateless: true)]
	public function getHealthCheck(Request $request): Response
	{
		$requestTime = $request->server->get('REQUEST_TIME_FLOAT');
		$currentTime = microtime(true);
		$executionTime = round(($currentTime - $requestTime) * 1000, 3);

		return new Response(
			sprintf(ASCII_INDEX, $executionTime),
			Response::HTTP_OK,
			[
				'Content-Type' => 'text/plain; charset=utf-8'
			]
		);
	}
}