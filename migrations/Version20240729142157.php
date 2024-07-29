<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240729142157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movement ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7727ACA70 FOREIGN KEY (parent_id) REFERENCES movement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_F4DD95F7727ACA70 ON movement (parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE movement DROP CONSTRAINT FK_F4DD95F7727ACA70');
        $this->addSql('DROP INDEX IDX_F4DD95F7727ACA70');
        $this->addSql('ALTER TABLE movement DROP parent_id');
    }
}
