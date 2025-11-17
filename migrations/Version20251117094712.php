<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117094712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE card_variant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE card_variant (id INT NOT NULL, card_id VARCHAR(50) NOT NULL, type VARCHAR(10) NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, cardmarket NUMERIC(10, 2) DEFAULT NULL, tcgplayer NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A65935DB4ACC9A20 ON card_variant (card_id)');
        $this->addSql('ALTER TABLE card_variant ADD CONSTRAINT FK_A65935DB4ACC9A20 FOREIGN KEY (card_id) REFERENCES cards (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cards DROP tcgplayer');
        $this->addSql('ALTER TABLE cards DROP cardmarket');
        $this->addSql('ALTER TABLE collection ADD variant VARCHAR(10) DEFAULT \'normal\' NOT NULL');
        $this->addSql('ALTER TABLE collection DROP languages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE card_variant_id_seq CASCADE');
        $this->addSql('ALTER TABLE card_variant DROP CONSTRAINT FK_A65935DB4ACC9A20');
        $this->addSql('DROP TABLE card_variant');
        $this->addSql('ALTER TABLE cards ADD tcgplayer JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE cards ADD cardmarket JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE collection ADD languages JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE collection DROP variant');
    }
}
