<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925161728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE custom_event_media_object (id UUID NOT NULL, file_name VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN custom_event_media_object.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event ADD media_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN custom_event.media_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event ADD CONSTRAINT FK_F8A3F2CEEA9FDD75 FOREIGN KEY (media_id) REFERENCES custom_event_media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F8A3F2CEEA9FDD75 ON custom_event (media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE custom_event DROP CONSTRAINT FK_F8A3F2CEEA9FDD75');
        $this->addSql('DROP TABLE custom_event_media_object');
        $this->addSql('DROP INDEX UNIQ_F8A3F2CEEA9FDD75');
        $this->addSql('ALTER TABLE custom_event DROP media_id');
    }
}
