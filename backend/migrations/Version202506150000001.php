<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202506150000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deleted_at column to employees table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE employees ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE employees DROP COLUMN deleted_at');
    }
} 