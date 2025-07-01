<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701215711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE cards_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE reset_password_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE boosters (name VARCHAR(50) NOT NULL, logo VARCHAR(255) DEFAULT NULL, artwork_front VARCHAR(255) DEFAULT NULL, artwork_back VARCHAR(255) DEFAULT NULL, PRIMARY KEY(name))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cards (id VARCHAR(50) NOT NULL, set_id VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, supertype VARCHAR(50) DEFAULT NULL, subtypes JSON DEFAULT NULL, hp VARCHAR(10) DEFAULT NULL, types JSON DEFAULT NULL, evolves_from VARCHAR(50) DEFAULT NULL, evolves_to JSON DEFAULT NULL, rules JSON DEFAULT NULL, ancient_trait JSON DEFAULT NULL, abilities JSON DEFAULT NULL, attacks JSON DEFAULT NULL, weaknesses JSON DEFAULT NULL, resistances JSON DEFAULT NULL, retreat_cost JSON DEFAULT NULL, converted_retreat_cost INT DEFAULT NULL, number VARCHAR(20) DEFAULT NULL, artist VARCHAR(255) DEFAULT NULL, rarity VARCHAR(100) DEFAULT NULL, flavor_text TEXT DEFAULT NULL, national_pokedex_numbers JSON DEFAULT NULL, legalities JSON DEFAULT NULL, regulation_mark VARCHAR(5) DEFAULT NULL, images JSON DEFAULT NULL, tcgplayer JSON DEFAULT NULL, cardmarket JSON DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C258FD10FB0D18 ON cards (set_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE card_booster (card_id VARCHAR(50) NOT NULL, booster_name VARCHAR(50) NOT NULL, PRIMARY KEY(card_id, booster_name))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B86C15DB4ACC9A20 ON card_booster (card_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B86C15DBE7085F09 ON card_booster (booster_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN reset_password_request.requested_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN reset_password_request.expires_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sets (id VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, series VARCHAR(100) DEFAULT NULL, printed_total INT DEFAULT NULL, total INT DEFAULT NULL, legalities JSON DEFAULT NULL, ptcgo_code VARCHAR(20) DEFAULT NULL, release_date DATE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, images JSON DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, api_token VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cards ADD CONSTRAINT FK_4C258FD10FB0D18 FOREIGN KEY (set_id) REFERENCES sets (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_booster ADD CONSTRAINT FK_B86C15DB4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_booster ADD CONSTRAINT FK_B86C15DBE7085F09 FOREIGN KEY (booster_name) REFERENCES boosters (name) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE cards_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE reset_password_request_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE user_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE cards DROP CONSTRAINT FK_4C258FD10FB0D18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_booster DROP CONSTRAINT FK_B86C15DB4ACC9A20
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE card_booster DROP CONSTRAINT FK_B86C15DBE7085F09
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP CONSTRAINT FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE boosters
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cards
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE card_booster
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
