<?php

namespace App\Infrastructure\Command;

use DateTime;
use App\Domain\Entity\Link;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\Console\Command\Command;
use App\Infrastructure\Repository\LinkRepository;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * Daily task deleting the links expired for more than a day or left unvisited for more than a year.
 */
#[AsPeriodicTask('1 day', from: 'midnight')]
#[AsCommand('app:shortcut-cleanup', 'Deletes expired shortcut links from the database.')]
final class OutdatedShortcutCleanup extends Command
{
    private readonly LinkRepository $repository;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository(Link::class);

        parent::__construct();
    }

    /**
     * Finds the links expired for more than a day or last visited more than a year ago.
     *
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
     * Schedules the deletion of the link, without flushing it yet.
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
     * Deletes every outdated link, then flushes them all at once.
     * @throws UniqueConstraintViolationException|ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = 0;

        foreach ($this->getExpiredLinks() as $link) {
            $this->deleteExpiredLink($io, $link);

            $count++;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Deleted %d expired shortcut link(s).', $count));

        return Command::SUCCESS;
    }
}
