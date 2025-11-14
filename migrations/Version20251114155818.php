<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114155818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prices ADD average_price NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices DROP market_price_fr');
        $this->addSql('ALTER TABLE prices DROP low_price_fr');
        $this->addSql('ALTER TABLE prices DROP high_price_fr');
        $this->addSql('ALTER TABLE prices DROP average_price_fr');
        $this->addSql('ALTER TABLE prices DROP market_price_en');
        $this->addSql('ALTER TABLE prices DROP low_price_en');
        $this->addSql('ALTER TABLE prices DROP high_price_en');
        $this->addSql('ALTER TABLE prices DROP average_price_en');
        $this->addSql('ALTER TABLE prices DROP market_price_jp');
        $this->addSql('ALTER TABLE prices DROP low_price_jp');
        $this->addSql('ALTER TABLE prices DROP high_price_jp');
        $this->addSql('ALTER TABLE prices DROP average_price_jp');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE prices ADD low_price_fr NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_fr NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_fr NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD market_price_en NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD low_price_en NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_en NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_en NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD market_price_jp NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD low_price_jp NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_jp NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_jp NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices RENAME COLUMN average_price TO market_price_fr');
    }
}
