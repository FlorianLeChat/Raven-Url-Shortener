<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\LinkApiKey;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Repository of the API keys.
 *
 * @extends ServiceEntityRepository<LinkApiKey>
 */
final class LinkApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkApiKey::class);
    }
}
