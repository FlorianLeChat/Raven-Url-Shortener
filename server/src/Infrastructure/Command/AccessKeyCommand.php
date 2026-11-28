<?php

namespace App\Infrastructure\Command;

use Random\RandomException;
use Symfony\Component\Uid\Uuid;
use App\Domain\Entity\AccessKey;
use Psr\Cache\InvalidArgumentException;
use App\Domain\Enum\AccessKeyCapability;
use Doctrine\ORM\Exception\ORMException;
use App\Domain\Service\AccessKeyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Infrastructure\Repository\AccessKeyRepository;
use App\Infrastructure\Exception\DataValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Creates, lists and revokes the access keys from the server, as the administrator routes do,
 *  without handing out the administrator API key: `app:access-key create|list|revoke`.
 */
#[AsCommand('app:access-key', 'Creates, lists or revokes the access keys.')]
final class AccessKeyCommand extends Command
{
    private const ACTIONS = ['create', 'list', 'revoke'];

    private const DATE_FORMAT = 'Y-m-d H:i:s T';

    public function __construct(
        private readonly AccessKeyService $service,
        private readonly AccessKeyRepository $repository
    ) {
        parent::__construct();
    }

    /**
     * Declares the action, its target and the options of a creation.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, implode(', ', self::ACTIONS))
            ->addArgument('target', InputArgument::OPTIONAL, 'Name of the key to create, or UUID of the key to revoke')
            ->addOption(
                'capability',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Capability granted by the key to create: ' . implode(', ', AccessKeyCapability::values())
            )
            ->addOption(
                'origin',
                'o',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Browser origin allowed with the private_api capability'
            )
            ->addOption(
                'expiration',
                null,
                InputOption::VALUE_REQUIRED,
                'Expiration date, between tomorrow and one year from now; left out, the key never expires'
            );
    }

    /**
     * Reads an array option as a list of strings.
     *
     * @return list<string>
     */
    private function getListOption(InputInterface $input, string $name): array
    {
        $values = $input->getOption($name);

        return is_array($values) ? array_values(array_filter($values, 'is_string')) : [];
    }

    /**
     * Reads a string argument or option, empty when it is missing.
     */
    private function getString(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Prints every violation of a refused creation, one line per field.
     */
    private function printViolations(SymfonyStyle $io, DataValidationException $exception): void
    {
        $lines = [];

        foreach ($exception->getViolations() as $field => $violations) {
            foreach ($violations as $violation) {
                $lines[] = sprintf('%s: %s', $field, $this->getString($violation['message'] ?? null));
            }
        }

        $io->error($lines);
    }

    /**
     * Creates the key and prints its clear value, shown this once.
     * @throws RandomException|UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    private function create(InputInterface $input, SymfonyStyle $io): int
    {
        try {
            $accessKey = $this->service->createAccessKey(
                $this->getString($input->getArgument('target')),
                $this->getListOption($input, 'capability'),
                $this->getListOption($input, 'origin'),
                $this->getString($input->getOption('expiration'))
            );
        } catch (DataValidationException $exception) {
            $this->printViolations($io, $exception);

            return Command::FAILURE;
        }

        $io->success(sprintf('Access key "%s" created (%s).', $accessKey->getName(), $accessKey->getId()));
        $io->writeln((string) $accessKey->getPlainKey());
        $io->warning('Store this key now: it will never be shown again.');

        return Command::SUCCESS;
    }

    /**
     * Prints every key, the most recent first, without their values.
     */
    private function list(SymfonyStyle $io): int
    {
        $rows = array_map(static fn (AccessKey $accessKey) => [
            $accessKey->getId(),
            $accessKey->getName(),
            implode(', ', $accessKey->getCapabilities()),
            implode(', ', $accessKey->getAllowedOrigins()),
            $accessKey->isActive() ? 'yes' : 'no',
            $accessKey->getExpiresAt()?->format(self::DATE_FORMAT) ?? 'never',
            $accessKey->getRevokedAt()?->format(self::DATE_FORMAT) ?? '-'
        ], $this->service->listAccessKeys());

        $io->table(['Id', 'Name', 'Capabilities', 'Origins', 'Active', 'Expires', 'Revoked'], $rows);

        return Command::SUCCESS;
    }

    /**
     * Revokes the key reached by its UUID.
     * @throws UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    private function revoke(InputInterface $input, SymfonyStyle $io): int
    {
        $id = $this->getString($input->getArgument('target'));
        $accessKey = Uuid::isValid($id) ? $this->repository->find($id) : null;

        if ($accessKey === null) {
            $io->error(sprintf('No access key has the identifier "%s".', $id));

            return Command::FAILURE;
        }

        try {
            $this->service->revokeAccessKey($accessKey);
        } catch (ConflictHttpException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Access key "%s" revoked.', $accessKey->getName()));

        return Command::SUCCESS;
    }

    /**
     * Runs the action given as first argument.
     * @throws RandomException|UniqueConstraintViolationException|ORMException|InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        return match ($input->getArgument('action')) {
            'create' => $this->create($input, $io),
            'list' => $this->list($io),
            'revoke' => $this->revoke($input, $io),
            default => $this->failOnUnknownAction($io)
        };
    }

    /**
     * Lists the known actions.
     */
    private function failOnUnknownAction(SymfonyStyle $io): int
    {
        $io->error(sprintf('Unknown action. Use one of: %s.', implode(', ', self::ACTIONS)));

        return Command::FAILURE;
    }
}
