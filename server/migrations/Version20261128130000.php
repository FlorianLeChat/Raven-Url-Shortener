<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Renames the table of the link API keys, so the name `api_key` no longer hides which keys it holds.
 * The index and the foreign key take the names Doctrine computes from the new table name.
 */
final class Version20261128130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename the api_key table to link_api_key';
    }

    /**
     * Renames the table, its primary key, its unique index and its foreign key, keeping every row.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE api_key RENAME TO link_api_key');
        $this->addSql('ALTER INDEX api_key_pkey RENAME TO link_api_key_pkey');
        $this->addSql('ALTER INDEX UNIQ_C912ED9DADA40271 RENAME TO UNIQ_B474B768ADA40271');
        $this->addSql('ALTER TABLE link_api_key RENAME CONSTRAINT FK_C912ED9DADA40271 TO FK_B474B768ADA40271');
    }

    /**
     * Gives the table and its constraints their former names back.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE link_api_key RENAME CONSTRAINT FK_B474B768ADA40271 TO FK_C912ED9DADA40271');
        $this->addSql('ALTER INDEX UNIQ_B474B768ADA40271 RENAME TO UNIQ_C912ED9DADA40271');
        $this->addSql('ALTER INDEX link_api_key_pkey RENAME TO api_key_pkey');
        $this->addSql('ALTER TABLE link_api_key RENAME TO api_key');
    }
}
