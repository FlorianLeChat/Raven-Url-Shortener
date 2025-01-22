<?php

namespace App\Command;

use DateTime;
use App\Domain\Entity\Link;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour le nettoyage des liens raccourcis expirés.
 */
#[AsCommand("app:shortcup-cleanup", "Deletes expired shortcut links from the database.")]
final class OutdatedShortcutCleanup extends Command
{
	/**
	 * Initialisation des dépendances de la commande.
	 */
	public function __construct(private readonly EntityManagerInterface $entityManager)
	{
		parent::__construct();
	}

	/**
	 * Exécution de la commande.
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		// Récupération de tous les liens raccourcis expirés.
		$repository = $this->entityManager->getRepository(Link::class);
		$query = $repository->createQueryBuilder("u");
		$query->where($query->expr()->lte("u.createdAt", ":past"))
			->setParameter("past", new DateTime("-1 day"), Types::DATETIME_MUTABLE);

		// Parcours de tous les liens raccourcis expirés pour les supprimer.
		$io = new SymfonyStyle($input, $output);
		$count = 0;

		foreach ($query->getQuery()->getResult() as $link)
		{
			$io->text(sprintf("Deleting shortcut \"%s (%d)\"...", $link->getUrl(), $link->getId()));
			$repository->remove($link);

			$count++;
		}

		// Sauvegarde des modifications dans la base de données
		//  et fin de la commande.
		$this->entityManager->flush();

		$io->success("Deleted $count expired shortcut link(s).");

		return Command::SUCCESS;
	}
}