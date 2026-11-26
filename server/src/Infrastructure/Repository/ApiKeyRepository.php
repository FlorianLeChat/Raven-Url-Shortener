<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ApiKey;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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

    /**
     * Persists the key, and writes it at once when `$flush` is set.
     * @throws UniqueConstraintViolationException|ORMException
     */
    public function save(ApiKey $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
