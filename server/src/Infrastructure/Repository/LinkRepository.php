<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Link;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Répertoire des méthodes pour les liens raccourcis.
 * @extends ServiceEntityRepository<Link>
 */
final class LinkRepository extends ServiceEntityRepository
{
	/**
	 * Constructeur de la classe.
	 */
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Link::class);
	}

	/**
	 * Création ou mise à jour d'un lien raccourci.
	 */
	public function save(Link $entity, bool $flush = false): void
	{
		$this->getEntityManager()->persist($entity);

		if ($flush)
		{
			$this->getEntityManager()->flush();
		}
	}

	/**
	 * Suppression d'un lien raccourci.
	 */
	public function remove(Link $entity, bool $flush = false): void
	{
		$this->getEntityManager()->remove($entity);

		if ($flush)
		{
			$this->getEntityManager()->flush();
		}
	}
}
