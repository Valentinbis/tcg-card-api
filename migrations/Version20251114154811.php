<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114154811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prices ADD market_price_fr NUMERIC(10, 2) DEFAULT NULL');
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
        $this->addSql('ALTER TABLE prices DROP market_price_eur');
        $this->addSql('ALTER TABLE prices DROP low_price_eur');
        $this->addSql('ALTER TABLE prices DROP high_price_eur');
        $this->addSql('ALTER TABLE prices DROP average_price_eur');
        $this->addSql('ALTER TABLE prices DROP market_price_usd');
        $this->addSql('ALTER TABLE prices DROP low_price_usd');
        $this->addSql('ALTER TABLE prices DROP high_price_usd');
        $this->addSql('ALTER TABLE prices DROP average_price_usd');
        $this->addSql('ALTER TABLE prices DROP market_price_jpy');
        $this->addSql('ALTER TABLE prices DROP low_price_jpy');
        $this->addSql('ALTER TABLE prices DROP high_price_jpy');
        $this->addSql('ALTER TABLE prices DROP average_price_jpy');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE prices ADD market_price_eur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD low_price_eur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_eur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_eur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD market_price_usd NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD low_price_usd NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_usd NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_usd NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD market_price_jpy NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD low_price_jpy NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD high_price_jpy NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE prices ADD average_price_jpy NUMERIC(10, 2) DEFAULT NULL');
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
}
