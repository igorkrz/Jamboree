<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260331143439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add picture to users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD picture VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP picture');
    }
}
