<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ApiKey;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Répertoire des méthodes pour la gestion des clés API pour les liens raccourcis.
 * @extends ServiceEntityRepository<ApiKey>
 */
final class ApiKeyRepository extends ServiceEntityRepository
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ApiKey::class);
	}

	/**
	 * Création ou mise à jour d'une clé API.
	 */
	public function save(ApiKey $entity, bool $flush = false): void
	{
		$this->getEntityManager()->persist($entity);

		if ($flush)
		{
			$this->getEntityManager()->flush();
		}
	}
}
