<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240923173935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location and provider to event';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_provider (id UUID NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D4289A4C5E237E06 ON event_provider (name)');
        $this->addSql('COMMENT ON COLUMN event_provider.id IS \'(DC2Type:ulid)\'');
        $this->addSql('CREATE TABLE location (id UUID NOT NULL, venue VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN location.id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event ADD location_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_event DROP location');
        $this->addSql('COMMENT ON COLUMN custom_event.location_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event ADD CONSTRAINT FK_F8A3F2CE64D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F8A3F2CE64D218E ON custom_event (location_id)');
        $this->addSql('ALTER TABLE event ADD location_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD provider_id UUID NOT NULL');
        $this->addSql('ALTER TABLE event DROP location');
        $this->addSql('COMMENT ON COLUMN event.location_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN event.provider_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A53A8AA FOREIGN KEY (provider_id) REFERENCES event_provider (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3BAE0AA764D218E ON event (location_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A53A8AA ON event (provider_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7A53A8AA');
        $this->addSql('ALTER TABLE custom_event DROP CONSTRAINT FK_F8A3F2CE64D218E');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA764D218E');
        $this->addSql('DROP TABLE event_provider');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP INDEX IDX_3BAE0AA764D218E');
        $this->addSql('DROP INDEX IDX_3BAE0AA7A53A8AA');
        $this->addSql('ALTER TABLE event ADD location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP location_id');
        $this->addSql('ALTER TABLE event DROP provider_id');
        $this->addSql('DROP INDEX IDX_F8A3F2CE64D218E');
        $this->addSql('ALTER TABLE custom_event ADD location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE custom_event DROP location_id');
    }
}
