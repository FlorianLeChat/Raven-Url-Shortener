<?php

namespace App\Domain\Service;

use App\Kernel;
use App\Domain\Entity\Link;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Repository\LinkRepository;

/**
 * Checks the availability of a custom slug before the link is created.
 */
final readonly class CheckSlugService
{
    private LinkRepository $repository;

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager
    ) {
        $this->repository = $this->entityManager->getRepository(Link::class);
    }

    /**
     * Tells whether no link uses the `slug` field of the request body yet.
     */
    public function checkSlug(Request $request): bool
    {
        $this->logger->info(sprintf(Kernel::LOG_FUNCTION, basename(__FILE__), __NAMESPACE__, __FUNCTION__, __LINE__));

        $slug = $request->getPayload()->getString('slug');
        $result = $this->repository->findOneBy(['slug' => $slug]);

        return empty($result);
    }
}
