<?php

namespace App\Action;

use Exception;
use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\UpdateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoints updating a shortened link through its management API key.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class UpdateLinkAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Updates the single field named by `field` with `value`.
     * @throws UniqueConstraintViolationException|ORMException|Exception
     */
    #[Route('/link/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['PATCH'])]
    #[Route('/link/{slug}', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['PATCH'])]
    public function patchLink(Request $request, Link $link): JsonResponse
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $service = new UpdateLinkService(
            $link,
            $this->logger,
            $this->validator,
            $this->translator,
            $this->httpClient,
            $this->entityManager
        );

        $updatedLink = $service->patchLink($request);

        return new JsonResponse($updatedLink->toArray(), Response::HTTP_OK);
    }

    /**
     * Replaces the link with the request body, keeping the current value of every field left out.
     * @throws UniqueConstraintViolationException|ORMException|Exception
     */
    #[Route('/link/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['PUT'])]
    #[Route('/link/{slug}', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['PUT'])]
    public function replaceLink(Request $request, Link $link): JsonResponse
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $service = new UpdateLinkService(
            $link,
            $this->logger,
            $this->validator,
            $this->translator,
            $this->httpClient,
            $this->entityManager
        );

        $updatedLink = $service->replaceLink($request);

        return new JsonResponse($updatedLink->toArray(), Response::HTTP_OK);
    }
}
