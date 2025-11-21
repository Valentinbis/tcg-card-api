<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251117103220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_average NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_trend NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_min NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_max NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_holo NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_market NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_mid NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_high NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_direct NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_suggested NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_german_pro_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_low_ex_plus NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_avg1 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_avg7 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_avg30 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse_trend NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse_avg1 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse_avg7 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD cardmarket_reverse_avg30 NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_normal_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_normal_mid NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_normal_high NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_normal_market NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_normal_direct NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_reverse_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_reverse_mid NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_reverse_high NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_reverse_market NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_reverse_direct NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_holo_low NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_holo_mid NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_holo_high NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_holo_market NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE card_variant ADD tcgplayer_holo_direct NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_average');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_trend');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_min');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_max');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_holo');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_market');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_low');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_mid');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_high');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_direct');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_suggested');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_german_pro_low');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_low_ex_plus');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_avg1');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_avg7');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_avg30');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse_low');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse_trend');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse_avg1');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse_avg7');
        $this->addSql('ALTER TABLE card_variant DROP cardmarket_reverse_avg30');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_normal_low');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_normal_mid');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_normal_high');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_normal_market');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_normal_direct');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_reverse_low');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_reverse_mid');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_reverse_high');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_reverse_market');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_reverse_direct');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_holo_low');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_holo_mid');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_holo_high');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_holo_market');
        $this->addSql('ALTER TABLE card_variant DROP tcgplayer_holo_direct');
    }
}
