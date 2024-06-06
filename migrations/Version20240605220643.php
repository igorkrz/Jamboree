<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240605220643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create custom events table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE custom_event (id UUID NOT NULL, user_id UUID NOT NULL, internal_code VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, holding_date TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F8A3F2CEA76ED395 ON custom_event (user_id)');
        $this->addSql('COMMENT ON COLUMN custom_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN custom_event.user_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE custom_event ADD CONSTRAINT FK_F8A3F2CEA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_event ADD custom_event_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE user_event ALTER event_id DROP NOT NULL');
        $this->addSql('COMMENT ON COLUMN user_event.custom_event_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FF94250FEF FOREIGN KEY (custom_event_id) REFERENCES custom_event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D96CF1FF94250FEF ON user_event (custom_event_id)');
        $this->addSql('ALTER INDEX uniq_8d93d649e7927c74 RENAME TO UNIQ_1483A5E9E7927C74');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_event DROP CONSTRAINT FK_D96CF1FF94250FEF');
        $this->addSql('ALTER TABLE custom_event DROP CONSTRAINT FK_F8A3F2CEA76ED395');
        $this->addSql('DROP TABLE custom_event');
        $this->addSql('DROP INDEX IDX_D96CF1FF94250FEF');
        $this->addSql('ALTER TABLE user_event DROP custom_event_id');
        $this->addSql('ALTER INDEX uniq_1483a5e9e7927c74 RENAME TO uniq_8d93d649e7927c74');
    }
}
