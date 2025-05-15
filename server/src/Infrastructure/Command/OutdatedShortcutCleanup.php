<?php

namespace App\Infrastructure\Command;

use DateTime;
use App\Domain\Entity\Link;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour le nettoyage des liens raccourcis expirés.
 */
#[AsCommand('app:shortcut-cleanup', 'Deletes expired shortcut links from the database.')]
final class OutdatedShortcutCleanup extends Command
{
	/**
	 * Répertoire des méthodes pour les liens raccourcis.
	 */
	private readonly LinkRepository $repository;

	/**
	 * Initialisation des dépendances de la commande.
	 */
	public function __construct(private readonly EntityManagerInterface $entityManager)
	{
		$this->repository = $this->entityManager->getRepository(Link::class);

		parent::__construct();
	}

	/**
	 * Récupération de tous les liens raccourcis expirés
	 *  depuis la base de données.
	 * @return Link[]
	 */
	private function getExpiredLinks(): array
	{
		$query = $this->repository->createQueryBuilder('link');
		$query->where($query->expr()->lte('link.expiresAt', ':oneDayAgo'))
			->orWhere($query->expr()->lte('link.visitedAt', ':oneYearAgo'))
			->setParameter('oneDayAgo', new DateTime('-1 day'), Types::DATETIME_MUTABLE)
			->setParameter('oneYearAgo', new DateTime('-1 year'), Types::DATETIME_MUTABLE);

		/** @var Link[] */
		return $query->getQuery()->getResult();
	}

	/**
	 * Suppression d'un lien raccourci expiré avec journalisation.
	 */
	private function deleteExpiredLink(SymfonyStyle $io, Link $link): void
	{
		$io->text(sprintf(
			'Deleting shortcut \'%s (%s)\'...',
			$link->getUrl(),
			$link->getId()
		));

		$this->entityManager->remove($link);
	}

	/**
	 * Exécution de la commande.
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$count = 0;

		foreach ($this->getExpiredLinks() as $link)
		{
			$this->deleteExpiredLink($io, $link);

			$count++;
		}

		$this->entityManager->flush();

		$io->success(sprintf('Deleted %d expired shortcut link(s).', $count));

		return Command::SUCCESS;
	}
}