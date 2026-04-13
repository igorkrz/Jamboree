<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413095057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add bio to artist';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist ADD summary TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE artist ADD bio TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist DROP summary');
        $this->addSql('ALTER TABLE artist DROP bio');
    }
}
