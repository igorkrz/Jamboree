<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240521000204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user event table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_event (id UUID NOT NULL, user_id UUID NOT NULL, event_id UUID NOT NULL, attending BOOLEAN DEFAULT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D96CF1FFA76ED395 ON user_event (user_id)');
        $this->addSql('CREATE INDEX IDX_D96CF1FF71F7E88B ON user_event (event_id)');
        $this->addSql('COMMENT ON COLUMN user_event.id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN user_event.user_id IS \'(DC2Type:ulid)\'');
        $this->addSql('COMMENT ON COLUMN user_event.event_id IS \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_event ADD CONSTRAINT FK_D96CF1FF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_event DROP CONSTRAINT FK_D96CF1FFA76ED395');
        $this->addSql('ALTER TABLE user_event DROP CONSTRAINT FK_D96CF1FF71F7E88B');
        $this->addSql('DROP TABLE user_event');
    }
}
