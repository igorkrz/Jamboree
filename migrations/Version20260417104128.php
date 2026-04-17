<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260417104128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implement artists pictures';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE artist_media_object (id UUID NOT NULL, file_name VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE artist ADD media_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE artist ADD CONSTRAINT FK_1599687EE45BDBF FOREIGN KEY (media_id) REFERENCES artist_media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1599687EE45BDBF ON artist (media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist DROP CONSTRAINT FK_1599687EE45BDBF');
        $this->addSql('DROP TABLE artist_media_object');
        $this->addSql('DROP INDEX UNIQ_1599687EE45BDBF');
        $this->addSql('ALTER TABLE artist DROP media_id');
    }
}
