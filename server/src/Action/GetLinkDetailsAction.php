<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Service\GetLinkDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class GetLinkDetailsAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Cache(maxage: 3600, public: true, mustRevalidate: true)]
    #[Route('/link/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['GET'])]
    #[Route('/link/{slug}', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['GET'])]
    public function getLinkDetails(Request $request, Link $link): JsonResponse
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $service = new GetLinkDetailsService(
            $this->logger,
            $this->validator,
            $this->translator,
            $this->httpClient,
            $this->entityManager
        );

        $link = $service->getLinkDetails($request, $link);

        return new JsonResponse($link->toArray(), Response::HTTP_OK);
    }
}
