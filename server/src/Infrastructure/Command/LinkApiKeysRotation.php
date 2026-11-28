<?php

namespace App\Infrastructure\Command;

use DateTime;
use Random\RandomException;
use Doctrine\DBAL\Types\Types;
use App\Domain\Entity\LinkApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Factory\LinkApiKeyFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Infrastructure\Repository\LinkApiKeyRepository;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Daily task rotating the API keys expired for more than a day.
 * The new keys are never disclosed: rotation ends API access to the links.
 */
#[AsPeriodicTask('1 day', from: 'midnight')]
#[AsCommand('app:api-keys-rotation', 'Rotates expired API keys for shortcut links.')]
final class LinkApiKeysRotation extends Command
{
    private readonly LinkApiKeyRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(LinkApiKey::class);

        parent::__construct();
    }

    /**
     * Finds the keys whose expiration date is more than a day old.
     *
     * @return LinkApiKey[]
     */
    private function getExpiredApiKeys(): array
    {
        $query = $this->repository->createQueryBuilder('key');
        $query->where($query->expr()->lte('key.expiresAt', ':oneDayAgo'))
            ->setParameter('oneDayAgo', new DateTime('-1 day'), Types::DATETIME_MUTABLE);

        /** @var LinkApiKey[] */
        return $query->getQuery()->getResult();
    }

    /**
     * Gives the key a new value and a new expiration date, without flushing it yet.
     * @throws RandomException
     */
    private function rotateExpiredApiKey(SymfonyStyle $io, LinkApiKey $apiKey): void
    {
        $io->text(sprintf(
            'Rotating API key \'%s (%s)\'...',
            $apiKey->getKey(),
            $apiKey->getId()
        ));

        $apiKey = LinkApiKeyFactory::rotate($apiKey);

        $this->entityManager->persist($apiKey);
    }

    /**
     * Rotates every expired key, then saves them all at once.
     * @throws UniqueConstraintViolationException|ORMException|RandomException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;

        foreach ($this->getExpiredApiKeys() as $apiKey) {
            $this->rotateExpiredApiKey($io, $apiKey);

            $count++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Rotated %d expired API key(s).', $count));

        return Command::SUCCESS;
    }
}
