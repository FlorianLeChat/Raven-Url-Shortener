<?php

namespace App\Action;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\DeleteLinkService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Requirement\Requirement;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Endpoint deleting a shortened link through its management API key.
 */
#[Route('/v{version}', requirements: ['version' => '1'], stateless: true)]
final class DeleteLinkAction extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly DeleteLinkService $service
    ) {
    }

    /**
     * Deletes the link reached by its identifier or its slug and answers with an empty 204 response.
     * @throws UniqueConstraintViolationException|ORMException
     */
    #[Route('/link/{id}', requirements: ['id' => Requirement::UUID_V7], methods: ['DELETE'])]
    #[Route('/link/{slug}', requirements: ['slug' => Requirement::ASCII_SLUG], methods: ['DELETE'])]
    public function deleteLink(Request $request, Link $link): Response
    {
        $this->logger->debug(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $this->service->deleteLink($request, $link);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
