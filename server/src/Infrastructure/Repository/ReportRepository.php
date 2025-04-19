<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Report;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Répertoire des méthodes pour les signalements de liens raccourcis.
 * @extends ServiceEntityRepository<Report>
 */
final class ReportRepository extends ServiceEntityRepository
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Report::class);
	}

	/**
	 * Création ou mise à jour d'un signalement.
	 */
	public function save(Report $entity, bool $flush = false): void
	{
		$this->getEntityManager()->persist($entity);

		if ($flush)
		{
			$this->getEntityManager()->flush();
		}
	}
}
