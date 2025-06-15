<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240320000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted_at column to employees table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE employees ADD deleted_at TIMESTAMP NULL DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_deleted_at ON employees(deleted_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_deleted_at ON employees');
        $this->addSql('ALTER TABLE employees DROP deleted_at');
    }
} 