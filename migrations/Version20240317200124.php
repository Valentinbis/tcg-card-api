<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240317200124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bank_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE movement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE recurrence_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bank (id INT NOT NULL, user_id INT NOT NULL, balance INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D860BF7AA76ED395 ON bank (user_id)');
        $this->addSql('COMMENT ON COLUMN bank.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bank.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN category.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN category.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE history (id INT NOT NULL, user_id INT DEFAULT NULL, movement_id INT DEFAULT NULL, balance INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27BA704BA76ED395 ON history (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_27BA704B229E70A7 ON history (movement_id)');
        $this->addSql('COMMENT ON COLUMN history.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN history.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE movement (id INT NOT NULL, user_id INT DEFAULT NULL, recurrence_id INT DEFAULT NULL, category_id INT NOT NULL, amount INT NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F4DD95F7A76ED395 ON movement (user_id)');
        $this->addSql('CREATE INDEX IDX_F4DD95F72C414CE8 ON movement (recurrence_id)');
        $this->addSql('CREATE INDEX IDX_F4DD95F712469DE2 ON movement (category_id)');
        $this->addSql('COMMENT ON COLUMN movement.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN movement.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE recurrence (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, api_token VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
        $this->addSql('ALTER TABLE bank ADD CONSTRAINT FK_D860BF7AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B229E70A7 FOREIGN KEY (movement_id) REFERENCES movement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F72C414CE8 FOREIGN KEY (recurrence_id) REFERENCES recurrence (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bank_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE movement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE recurrence_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE bank DROP CONSTRAINT FK_D860BF7AA76ED395');
        $this->addSql('ALTER TABLE history DROP CONSTRAINT FK_27BA704BA76ED395');
        $this->addSql('ALTER TABLE history DROP CONSTRAINT FK_27BA704B229E70A7');
        $this->addSql('ALTER TABLE movement DROP CONSTRAINT FK_F4DD95F7A76ED395');
        $this->addSql('ALTER TABLE movement DROP CONSTRAINT FK_F4DD95F72C414CE8');
        $this->addSql('ALTER TABLE movement DROP CONSTRAINT FK_F4DD95F712469DE2');
        $this->addSql('DROP TABLE bank');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE history');
        $this->addSql('DROP TABLE movement');
        $this->addSql('DROP TABLE recurrence');
        $this->addSql('DROP TABLE "user"');
    }
}
