<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ApiKey;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Repository of the API keys.
 *
 * @extends ServiceEntityRepository<ApiKey>
 */
final class ApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
    }
}
