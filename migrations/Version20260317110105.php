<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317110105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove access tokens from database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_access_token');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_access_token (id UUID NOT NULL, token VARCHAR(255) NOT NULL, user_identifier VARCHAR(255) NOT NULL, ip_address VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, valid_until TIMESTAMP(0) WITH TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
    }
}
