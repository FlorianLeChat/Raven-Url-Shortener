<?php

namespace App\Infrastructure\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use App\Domain\Entity\AccessKey;
use Doctrine\Persistence\ManagerRegistry;
use App\Infrastructure\Security\SecretKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Repository of the access keys.
 *
 * @extends ServiceEntityRepository<AccessKey>
 */
final class AccessKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessKey::class);
    }

    /**
     * Finds the key by the hash of its clear value, through the unique index of the hashes.
     */
    public function findOneByPlainKey(string $plainKey): ?AccessKey
    {
        return $this->findOneBy(['key' => SecretKey::hash($plainKey)]);
    }

    /**
     * Finds the keys neither revoked nor expired.
     *
     * @return AccessKey[]
     */
    public function findActive(): array
    {
        $query = $this->createQueryBuilder('accessKey');
        $query->where('accessKey.revokedAt IS NULL')
            ->andWhere('accessKey.expiresAt IS NULL OR accessKey.expiresAt > :now')
            ->setParameter('now', new DateTimeImmutable(), Types::DATETIMETZ_IMMUTABLE);

        /** @var AccessKey[] */
        return $query->getQuery()->getResult();
    }
}
