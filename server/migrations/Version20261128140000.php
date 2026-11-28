<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Adds the access keys an administrator hands to trusted people, with their capabilities.
 */
final class Version20261128140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the access keys';
    }

    /**
     * Creates the table, with a unique index on the hashes the requests are matched against.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql(<<<'SQL'
            CREATE TABLE access_key (
                id UUID NOT NULL,
                name VARCHAR(100) NOT NULL,
                key VARCHAR(64) NOT NULL,
                capabilities JSON NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                expires_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                revoked_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EAD0F67C8A90ABA9 ON access_key (key)');
    }

    /**
     * Drops the table: every access key stops working.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('DROP TABLE access_key');
    }
}
