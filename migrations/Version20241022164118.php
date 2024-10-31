<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241022164118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tags for events, add more info to location';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE custom_event_tag (custom_event_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(custom_event_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_587C05F694250FEF ON custom_event_tag (custom_event_id)');
        $this->addSql('CREATE INDEX IDX_587C05F6BAD26311 ON custom_event_tag (tag_id)');
        $this->addSql('COMMENT ON COLUMN custom_event_tag.custom_event_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN custom_event_tag.tag_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE event_tag (event_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(event_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_1246725071F7E88B ON event_tag (event_id)');
        $this->addSql('CREATE INDEX IDX_12467250BAD26311 ON event_tag (tag_id)');
        $this->addSql('COMMENT ON COLUMN event_tag.event_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN event_tag.tag_id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE tag (id UUID NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7835E237E06 ON tag (name)');
        $this->addSql('COMMENT ON COLUMN tag.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event_tag ADD CONSTRAINT FK_587C05F694250FEF FOREIGN KEY (custom_event_id) REFERENCES custom_event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE custom_event_tag ADD CONSTRAINT FK_587C05F6BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_tag ADD CONSTRAINT FK_1246725071F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event_tag ADD CONSTRAINT FK_12467250BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE location ADD address_line VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD zip_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE location ADD country VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_event_tag DROP CONSTRAINT FK_587C05F694250FEF');
        $this->addSql('ALTER TABLE custom_event_tag DROP CONSTRAINT FK_587C05F6BAD26311');
        $this->addSql('ALTER TABLE event_tag DROP CONSTRAINT FK_1246725071F7E88B');
        $this->addSql('ALTER TABLE event_tag DROP CONSTRAINT FK_12467250BAD26311');
        $this->addSql('DROP TABLE custom_event_tag');
        $this->addSql('DROP TABLE event_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('ALTER TABLE location DROP address_line');
        $this->addSql('ALTER TABLE location DROP zip_code');
        $this->addSql('ALTER TABLE location DROP country');
    }
}
