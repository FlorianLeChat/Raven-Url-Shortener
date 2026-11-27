<?php

namespace App\Action;

use Exception;
use App\Kernel;
use Random\RandomException;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\CreateLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint creating a shortened link.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class CreateLinkAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CreateLinkService $service
    ) {
    }

    /**
     * Creates a link from the request body and answers it with a 201 status.
     * Validation, URL reachability and slug uniqueness errors are raised by CreateLinkService.
     * @throws UniqueConstraintViolationException|ORMException|RandomException|Exception
     */
    #[Route('/link', methods: ['POST'])]
    public function createLink(Request $request): JsonResponse
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $link = $this->service->createLink($request);

        return new JsonResponse($link->toArray(), Response::HTTP_CREATED);
    }
}
