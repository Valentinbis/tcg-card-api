<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112135631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_settings (id INT NOT NULL, user_id INT NOT NULL, cards_per_page INT DEFAULT 20 NOT NULL, default_view VARCHAR(10) DEFAULT \'grid\' NOT NULL, default_language VARCHAR(5) DEFAULT \'fr\' NOT NULL, show_card_numbers BOOLEAN DEFAULT true NOT NULL, show_prices BOOLEAN DEFAULT true NOT NULL, email_notifications BOOLEAN DEFAULT true NOT NULL, new_card_alerts BOOLEAN DEFAULT true NOT NULL, price_drop_alerts BOOLEAN DEFAULT false NOT NULL, weekly_report BOOLEAN DEFAULT false NOT NULL, profile_visibility VARCHAR(10) DEFAULT \'public\' NOT NULL, show_collection BOOLEAN DEFAULT true NOT NULL, show_wishlist BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C844C5A76ED395 ON user_settings (user_id)');
        $this->addSql('ALTER TABLE user_settings ADD CONSTRAINT FK_5C844C5A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX idx_cards_name');
        $this->addSql('DROP INDEX idx_cards_number');
        $this->addSql('DROP INDEX idx_user_api_token');
        $this->addSql('DROP INDEX idx_user_last_activity');
        $this->addSql('DROP INDEX idx_user_token_expires');
        $this->addSql('DROP INDEX idx_user_card_card');
        $this->addSql('DROP INDEX idx_user_card_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_settings_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_settings DROP CONSTRAINT FK_5C844C5A76ED395');
        $this->addSql('DROP TABLE user_settings');
        $this->addSql('CREATE INDEX idx_user_card_card ON user_card (card_id)');
        $this->addSql('CREATE INDEX idx_user_card_user ON user_card (user_id)');
        $this->addSql('CREATE INDEX idx_cards_name ON cards (name)');
        $this->addSql('CREATE INDEX idx_cards_number ON cards (number)');
        $this->addSql('CREATE INDEX idx_user_api_token ON "user" (api_token)');
        $this->addSql('CREATE INDEX idx_user_last_activity ON "user" (last_activity_at)');
        $this->addSql('CREATE INDEX idx_user_token_expires ON "user" (token_expires_at)');
    }
}
