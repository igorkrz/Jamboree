<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241001133851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove internal code from custom event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_event DROP internal_code');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_event ADD internal_code VARCHAR(255) DEFAULT NULL');
    }
}
