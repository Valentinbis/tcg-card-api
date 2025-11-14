<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114153212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE prices_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE prices (id INT NOT NULL, card_id VARCHAR(50) NOT NULL, market_price NUMERIC(10, 2) DEFAULT NULL, low_price NUMERIC(10, 2) DEFAULT NULL, high_price NUMERIC(10, 2) DEFAULT NULL, average_price NUMERIC(10, 2) DEFAULT NULL, source VARCHAR(20) NOT NULL, last_updated TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_price_card ON prices (card_id)');
        $this->addSql('CREATE INDEX idx_price_updated ON prices (last_updated)');
        $this->addSql('COMMENT ON COLUMN prices.last_updated IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN prices.created_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE prices_id_seq CASCADE');
        $this->addSql('DROP TABLE prices');
    }
}
