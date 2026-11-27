<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Initial schema of the links, their reports and their API keys.
 * The tables of an existing database were created by `doctrine:schema:update`: the migration is then skipped,
 *  and Doctrine records it as executed so the following ones apply on top of it.
 * The lock and cache tables are left out: their stores create them on first use.
 */
final class Version20261127140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema of the links, their reports and their API keys';
    }

    /**
     * Creates the three tables, unless the database already holds them.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->skipIf($schema->hasTable('link'), 'The tables already exist.');

        $this->addSql(<<<'SQL'
            CREATE TABLE link (
                id UUID NOT NULL,
                url TEXT NOT NULL,
                slug VARCHAR(50) NOT NULL,
                password VARCHAR(255) DEFAULT NULL,
                enabled BOOLEAN DEFAULT true NOT NULL,
                trusted BOOLEAN DEFAULT false NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                visited_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                expires_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_36AC99F1989D9B62 ON link (slug)');

        $this->addSql(<<<'SQL'
            CREATE TABLE api_key (
                id UUID NOT NULL,
                key VARCHAR(44) NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                expires_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                link_id UUID NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C912ED9DADA40271 ON api_key (link_id)');

        $this->addSql(<<<'SQL'
            CREATE TABLE report (
                id UUID NOT NULL,
                reason VARCHAR(500) NOT NULL,
                email VARCHAR(100) DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                link_id UUID NOT NULL,
                PRIMARY KEY (id)
            )
            SQL);
        $this->addSql('CREATE INDEX IDX_C42F7784ADA40271 ON report (link_id)');

        $this->addSql(
            'ALTER TABLE api_key ADD CONSTRAINT FK_C912ED9DADA40271'
            . ' FOREIGN KEY (link_id) REFERENCES link (id) NOT DEFERRABLE'
        );
        $this->addSql(
            'ALTER TABLE report ADD CONSTRAINT FK_C42F7784ADA40271'
            . ' FOREIGN KEY (link_id) REFERENCES link (id) NOT DEFERRABLE'
        );
    }

    /**
     * Drops the three tables with their data.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE api_key DROP CONSTRAINT FK_C912ED9DADA40271');
        $this->addSql('ALTER TABLE report DROP CONSTRAINT FK_C42F7784ADA40271');
        $this->addSql('DROP TABLE api_key');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE link');
    }
}
