<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127104246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE user_settings (id INT NOT NULL, user_id INT NOT NULL, cards_per_page INT DEFAULT 20 NOT NULL, default_view VARCHAR(10) DEFAULT \'grid\' NOT NULL, default_language VARCHAR(5) DEFAULT \'fr\' NOT NULL, show_card_numbers BOOLEAN DEFAULT true NOT NULL, show_prices BOOLEAN DEFAULT true NOT NULL, email_notifications BOOLEAN DEFAULT true NOT NULL, new_card_alerts BOOLEAN DEFAULT true NOT NULL, price_drop_alerts BOOLEAN DEFAULT false NOT NULL, weekly_report BOOLEAN DEFAULT false NOT NULL, profile_visibility VARCHAR(10) DEFAULT \'public\' NOT NULL, show_collection BOOLEAN DEFAULT true NOT NULL, show_wishlist BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_5c844c5a76ed395 ON user_settings (user_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE card_variant (id INT NOT NULL, card_id VARCHAR(50) NOT NULL, type VARCHAR(10) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, cardmarket NUMERIC(10, 2) DEFAULT NULL, tcgplayer NUMERIC(10, 2) DEFAULT NULL, cardmarket_average NUMERIC(10, 2) DEFAULT NULL, cardmarket_trend NUMERIC(10, 2) DEFAULT NULL, cardmarket_min NUMERIC(10, 2) DEFAULT NULL, cardmarket_max NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse NUMERIC(10, 2) DEFAULT NULL, cardmarket_holo NUMERIC(10, 2) DEFAULT NULL, tcgplayer_market NUMERIC(10, 2) DEFAULT NULL, tcgplayer_low NUMERIC(10, 2) DEFAULT NULL, tcgplayer_mid NUMERIC(10, 2) DEFAULT NULL, tcgplayer_high NUMERIC(10, 2) DEFAULT NULL, tcgplayer_direct NUMERIC(10, 2) DEFAULT NULL, cardmarket_suggested NUMERIC(10, 2) DEFAULT NULL, cardmarket_german_pro_low NUMERIC(10, 2) DEFAULT NULL, cardmarket_low_ex_plus NUMERIC(10, 2) DEFAULT NULL, cardmarket_avg1 NUMERIC(10, 2) DEFAULT NULL, cardmarket_avg7 NUMERIC(10, 2) DEFAULT NULL, cardmarket_avg30 NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse_low NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse_trend NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse_avg1 NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse_avg7 NUMERIC(10, 2) DEFAULT NULL, cardmarket_reverse_avg30 NUMERIC(10, 2) DEFAULT NULL, tcgplayer_normal_low NUMERIC(10, 2) DEFAULT NULL, tcgplayer_normal_mid NUMERIC(10, 2) DEFAULT NULL, tcgplayer_normal_high NUMERIC(10, 2) DEFAULT NULL, tcgplayer_normal_market NUMERIC(10, 2) DEFAULT NULL, tcgplayer_normal_direct NUMERIC(10, 2) DEFAULT NULL, tcgplayer_reverse_low NUMERIC(10, 2) DEFAULT NULL, tcgplayer_reverse_mid NUMERIC(10, 2) DEFAULT NULL, tcgplayer_reverse_high NUMERIC(10, 2) DEFAULT NULL, tcgplayer_reverse_market NUMERIC(10, 2) DEFAULT NULL, tcgplayer_reverse_direct NUMERIC(10, 2) DEFAULT NULL, tcgplayer_holo_low NUMERIC(10, 2) DEFAULT NULL, tcgplayer_holo_mid NUMERIC(10, 2) DEFAULT NULL, tcgplayer_holo_high NUMERIC(10, 2) DEFAULT NULL, tcgplayer_holo_market NUMERIC(10, 2) DEFAULT NULL, tcgplayer_holo_direct NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_a65935db4acc9a20 ON card_variant (card_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, api_token VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, token_expires_at TIMESTAMP(6) WITHOUT TIME ZONE DEFAULT NULL, last_activity_at TIMESTAMP(6) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_identifier_email ON "user" (email)');
        $this->addSql('COMMENT ON COLUMN "user".updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".token_expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN "user".last_activity_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE collection (id INT NOT NULL, user_id INT NOT NULL, card_id VARCHAR(50) NOT NULL, quantity INT DEFAULT 1 NOT NULL, condition VARCHAR(20) DEFAULT NULL, purchase_price NUMERIC(10, 2) DEFAULT NULL, purchase_date DATE DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, variant VARCHAR(10) DEFAULT \'normal\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_collection_card ON collection (card_id)');
        $this->addSql('CREATE INDEX idx_collection_user ON collection (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_card_collection ON collection (user_id, card_id)');
        $this->addSql('COMMENT ON COLUMN collection.purchase_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN collection.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN collection.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE cards (id VARCHAR(50) NOT NULL, set_id VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, supertype VARCHAR(50) DEFAULT NULL, subtypes JSON DEFAULT NULL, hp VARCHAR(10) DEFAULT NULL, types JSON DEFAULT NULL, evolves_from VARCHAR(50) DEFAULT NULL, evolves_to JSON DEFAULT NULL, rules JSON DEFAULT NULL, ancient_trait JSON DEFAULT NULL, abilities JSON DEFAULT NULL, attacks JSON DEFAULT NULL, weaknesses JSON DEFAULT NULL, resistances JSON DEFAULT NULL, retreat_cost JSON DEFAULT NULL, converted_retreat_cost INT DEFAULT NULL, number VARCHAR(50) DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, rarity VARCHAR(100) DEFAULT NULL, flavor_text TEXT DEFAULT NULL, national_pokedex_numbers JSON DEFAULT NULL, legalities JSON DEFAULT NULL, regulation_mark VARCHAR(5) DEFAULT NULL, images JSON DEFAULT NULL, name_fr VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_4c258fd10fb0d18 ON cards (set_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE boosters (name VARCHAR(50) NOT NULL, logo VARCHAR(255) DEFAULT NULL, artwork_front VARCHAR(255) DEFAULT NULL, artwork_back VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name))');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE prices (id INT NOT NULL, card_id VARCHAR(50) NOT NULL, market_price NUMERIC(10, 2) DEFAULT NULL, low_price NUMERIC(10, 2) DEFAULT NULL, high_price NUMERIC(10, 2) DEFAULT NULL, last_updated TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, average_price NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_price_card ON prices (card_id)');
        $this->addSql('CREATE INDEX idx_price_updated ON prices (last_updated)');
        $this->addSql('COMMENT ON COLUMN prices.last_updated IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prices.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE wishlist (id INT NOT NULL, user_id INT NOT NULL, card_id VARCHAR(50) NOT NULL, priority INT NOT NULL, notes TEXT DEFAULT NULL, max_price NUMERIC(10, 2) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, variant VARCHAR(10) DEFAULT \'normal\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_wishlist_card ON wishlist (card_id)');
        $this->addSql('CREATE INDEX idx_wishlist_user ON wishlist (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_card ON wishlist (user_id, card_id)');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE reset_password_request (id INT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_7ce748aa76ed395 ON reset_password_request (user_id)');
        $this->addSql('COMMENT ON COLUMN reset_password_request.requested_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN reset_password_request.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE sets (id VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, series VARCHAR(100) DEFAULT NULL, printed_total INT DEFAULT NULL, total INT DEFAULT NULL, legalities JSON DEFAULT NULL, ptcgo_code VARCHAR(20) DEFAULT NULL, release_date DATE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, images JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('CREATE TABLE card_booster (card_id VARCHAR(50) NOT NULL, booster_name VARCHAR(50) NOT NULL, PRIMARY KEY(card_id, booster_name))');
        $this->addSql('CREATE INDEX idx_b86c15db4acc9a20 ON card_booster (card_id)');
        $this->addSql('CREATE INDEX idx_b86c15dbe7085f09 ON card_booster (booster_name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE user_settings');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE card_variant');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE "user"');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE collection');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE cards');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE boosters');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE prices');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE wishlist');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE reset_password_request');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE sets');
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\PostgreSQL120Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\PostgreSQL120Platform'."
        );

        $this->addSql('DROP TABLE card_booster');
    }
}
