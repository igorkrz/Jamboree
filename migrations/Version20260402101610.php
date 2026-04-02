<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260402101610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implement artists for events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE artist (id UUID NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_15996875E237E06 ON artist (name)');
        $this->addSql('CREATE TABLE artist_tag (artist_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY (artist_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_DB8C3232B7970CF8 ON artist_tag (artist_id)');
        $this->addSql('CREATE INDEX IDX_DB8C3232BAD26311 ON artist_tag (tag_id)');
        $this->addSql('CREATE TABLE event_artist (event_id UUID NOT NULL, artist_id UUID NOT NULL, PRIMARY KEY (event_id, artist_id))');
        $this->addSql('CREATE INDEX IDX_33C0E1D571F7E88B ON event_artist (event_id)');
        $this->addSql('CREATE INDEX IDX_33C0E1D5B7970CF8 ON event_artist (artist_id)');
        $this->addSql('ALTER TABLE artist_tag ADD CONSTRAINT FK_DB8C3232B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE artist_tag ADD CONSTRAINT FK_DB8C3232BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_artist ADD CONSTRAINT FK_33C0E1D571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_artist ADD CONSTRAINT FK_33C0E1D5B7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artist_tag DROP CONSTRAINT FK_DB8C3232B7970CF8');
        $this->addSql('ALTER TABLE artist_tag DROP CONSTRAINT FK_DB8C3232BAD26311');
        $this->addSql('ALTER TABLE event_artist DROP CONSTRAINT FK_33C0E1D571F7E88B');
        $this->addSql('ALTER TABLE event_artist DROP CONSTRAINT FK_33C0E1D5B7970CF8');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE artist_tag');
        $this->addSql('DROP TABLE event_artist');
    }
}
