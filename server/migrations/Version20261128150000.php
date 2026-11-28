<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

/**
 * Adds the browser origins an access key may call a private API from.
 * The existing keys declare none: their holders keep calling from scripts or the allowed origins.
 */
final class Version20261128150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the allowed origins of the access keys';
    }

    /**
     * Adds the column, filled with an empty list for the existing keys, then drops the default
     *  the entity does not declare.
     */
    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql("ALTER TABLE access_key ADD allowed_origins JSON DEFAULT '[]' NOT NULL");
        $this->addSql('ALTER TABLE access_key ALTER allowed_origins DROP DEFAULT');
    }

    /**
     * Drops the column: the keys lose their origins.
     */
    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('ALTER TABLE access_key DROP allowed_origins');
    }
}
