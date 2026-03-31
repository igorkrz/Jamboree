<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327102312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implement google OAuth2 and google calendar for users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_oauth_token (id UUID NOT NULL, provider VARCHAR(50) NOT NULL, access_token TEXT NOT NULL, refresh_token TEXT DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_712F82BFA76ED395 ON user_oauth_token (user_id)');
        $this->addSql('ALTER TABLE user_oauth_token ADD CONSTRAINT FK_712F82BFA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('CREATE TABLE user_calendar (id UUID NOT NULL, calendar_id VARCHAR(255) NOT NULL, summary VARCHAR(255) DEFAULT NULL, etag VARCHAR(255) DEFAULT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8E244546A76ED395 ON user_calendar (user_id)');
        $this->addSql('ALTER TABLE user_calendar ADD CONSTRAINT FK_8E244546A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_oauth_token DROP CONSTRAINT FK_712F82BFA76ED395');
        $this->addSql('DROP TABLE user_oauth_token');

        $this->addSql('ALTER TABLE user_calendar DROP CONSTRAINT FK_8E244546A76ED395');
        $this->addSql('DROP TABLE user_calendar');
    }
}
