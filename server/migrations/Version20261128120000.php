<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Adds the review state of the reports and the locale of their reporter.
 * The existing reports become pending, in English: no administrator has reviewed them yet.
 */
final class Version20261128120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the review status, the review date and the locale of the reports';
    }

    /**
     * Adds the three columns, with defaults filling the existing rows.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql("ALTER TABLE report ADD locale VARCHAR(2) DEFAULT 'en' NOT NULL");
        $this->addSql("ALTER TABLE report ADD status VARCHAR(10) DEFAULT 'pending' NOT NULL");
        $this->addSql('ALTER TABLE report ADD reviewed_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL');
    }

    /**
     * Drops the three columns: the decisions of the administrators are lost.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE report DROP locale');
        $this->addSql('ALTER TABLE report DROP status');
        $this->addSql('ALTER TABLE report DROP reviewed_at');
    }
}
