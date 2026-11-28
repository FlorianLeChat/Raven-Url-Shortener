<?php

namespace App\Action;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Infrastructure\Security\CaptchaVerifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint giving the browser an ALTCHA challenge to solve before a write request to a private API.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class CaptchaChallengeAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CaptchaVerifier $captcha
    ) {
    }

    /**
     * Answers a new challenge, never stored by a cache: each one can be solved only once.
     * @throws NotFoundHttpException When the API is public and requires no CAPTCHA.
     */
    #[Route('/captcha', methods: ['GET'])]
    public function getChallenge(): JsonResponse
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        if (!$this->captcha->isRequired()) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->captcha->createChallenge(), headers: [
            'Cache-Control' => 'no-store'
        ]);
    }
}
