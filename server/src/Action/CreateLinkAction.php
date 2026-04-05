<?php

namespace App\Action;

use App\Kernel;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class CreateLinkAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/link', methods: ['POST'])]
    public function createLink(Request $request): JsonResponse
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $service = new CreateLinkService(
            $this->logger,
            $this->validator,
            $this->translator,
            $this->httpClient,
            $this->entityManager
        );

        $link = $service->createLink($request);

        return new JsonResponse($link->toArray(), Response::HTTP_CREATED);
    }
}
