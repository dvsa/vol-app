<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial doctrine migration to mark state after last liquibase run
 */
final class Version20241030095525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration capturing existing database state from Liquibase';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('-- Mark initial state of database from liquibase last run');
    }

    public function down(Schema $schema): void
    {
        // Cant revert this as it is the initial state
        $this->throwIrreversibleMigrationException();
    }
}
