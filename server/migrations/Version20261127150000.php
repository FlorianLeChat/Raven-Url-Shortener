<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Stores the API keys as SHA-256 hashes, as ApiKeyFactory::hashKey() computes them.
 * The clear keys are lost: the migration cannot be reverted.
 */
final class Version20261127150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the API keys as SHA-256 hashes';
    }

    /**
     * Widens the column to the 64 hexadecimal characters of the hash, then hashes the existing keys.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE api_key ALTER key TYPE VARCHAR(64)');
        $this->addSql("UPDATE api_key SET key = encode(sha256(convert_to(key, 'UTF8')), 'hex')");
    }

    /**
     * Always fails: the clear keys cannot be computed back from their hashes.
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
