<?php

namespace App\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Root endpoint, used as a health check by Docker and as a landing page for curious visitors.
 */
final class ServerIndexAction extends AbstractController
{
    private const ASCII_INDEX = <<<EOT
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

Version: %s.
Server is running. Response time: %s ms.
Ready to handle requests.
API documentation available at https://docs.url.florian-dev.fr.
EOT;

    /**
     * @param string $version Version of the deployed API, set by the CI/CD pipeline.
     */
    public function __construct(private readonly string $version)
    {
    }

    /**
     * Answers a plain text banner with the time spent handling the request and the version of the API.
     * `REQUEST_TIME_FLOAT` is missing when the request does not come from a web server: the time then starts now.
     */
    #[Route('/', methods: ['GET'], stateless: true)]
    public function getHealthCheck(Request $request): Response
    {
        $requestTime = $request->server->get('REQUEST_TIME_FLOAT');

        if (!is_float($requestTime)) {
            $requestTime = microtime(true);
        }

        $currentTime = microtime(true);
        $executionTime = round(($currentTime - $requestTime) * 1000, 3);

        return new Response(
            sprintf(self::ASCII_INDEX, $this->version, $executionTime),
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]
        );
    }
}
