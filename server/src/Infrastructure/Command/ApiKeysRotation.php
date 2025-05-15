<?php

namespace App\Infrastructure\Command;

use DateTime;
use App\Domain\Entity\ApiKey;
use Doctrine\DBAL\Types\Types;
use App\Domain\Factory\ApiKeyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use App\Infrastructure\Repository\ApiKeyRepository;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour la rotation des clés API de gestion expirées.
 */
#[AsCommand('app:api-keys-rotation', 'Rotates expired API keys for shortcut links.')]
final class ApiKeysRotation extends Command
{
	/**
	 * Répertoire des méthodes pour les clés API.
	 */
	private readonly ApiKeyRepository $repository;

	/**
	 * Initialisation des dépendances de la commande.
	 */
	public function __construct(private readonly EntityManagerInterface $entityManager)
	{
		$this->repository = $this->entityManager->getRepository(ApiKey::class);

		parent::__construct();
	}

	/**
	 * Récupération de toutes les clés API expirées depuis la base de données.
	 * @return ApiKey[]
	 */
	private function getExpiredApiKeys(): array
	{
		$query = $this->repository->createQueryBuilder('key');
		$query->where($query->expr()->lte('key.expiresAt', ':oneDayAgo'))
			->setParameter('oneDayAgo', new DateTime('-1 day'), Types::DATETIME_MUTABLE);

		/** @var ApiKey[] */
		return $query->getQuery()->getResult();
	}

	/**
	 * Rotation d'une clé API expirée avec journalisation.
	 */
	private function rotateExpiredApiKey(SymfonyStyle $io, ApiKey $apiKey): void
	{
		$io->text(sprintf(
			'Rotating API key \'%s (%s)\'...',
			$apiKey->getKey(),
			$apiKey->getId()
		));

		$apiKey = ApiKeyFactory::rotate($apiKey);

		$this->entityManager->persist($apiKey);
	}

	/**
	 * Exécution de la commande.
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);
		$count = 0;

		foreach ($this->getExpiredApiKeys() as $apiKey)
		{
			$this->rotateExpiredApiKey($io, $apiKey);

			$count++;
		}

		$this->entityManager->flush();

		$io->success(sprintf('Rotated %d expired API key(s).', $count));

		return Command::SUCCESS;
	}
}