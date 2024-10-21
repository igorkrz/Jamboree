<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240930162920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create event media object for storing scraped images';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_media_object (id UUID NOT NULL, file_name VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN event_media_object.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE event ADD media_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN event.media_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7EA9FDD75 FOREIGN KEY (media_id) REFERENCES event_media_object (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7EA9FDD75 ON event (media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7EA9FDD75');
        $this->addSql('DROP TABLE event_media_object');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7EA9FDD75');
        $this->addSql('ALTER TABLE event DROP media_id');
    }
}
