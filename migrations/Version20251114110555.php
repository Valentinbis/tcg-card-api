<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114110555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE collection_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE collection (id INT NOT NULL, user_id INT NOT NULL, card_id VARCHAR(50) NOT NULL, quantity INT DEFAULT 1 NOT NULL, condition VARCHAR(20) DEFAULT NULL, purchase_price NUMERIC(10, 2) DEFAULT NULL, purchase_date DATE DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(6) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_collection_user ON collection (user_id)');
        $this->addSql('CREATE INDEX idx_collection_card ON collection (card_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_card_collection ON collection (user_id, card_id)');
        $this->addSql('COMMENT ON COLUMN collection.purchase_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('COMMENT ON COLUMN collection.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN collection.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE collection ADD CONSTRAINT FK_FC4D6532A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE wishlist ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wishlist ALTER priority DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE collection_id_seq CASCADE');
        $this->addSql('ALTER TABLE collection DROP CONSTRAINT FK_FC4D6532A76ED395');
        $this->addSql('DROP TABLE collection');
        $this->addSql('CREATE SEQUENCE wishlist_id_seq');
        $this->addSql('SELECT setval(\'wishlist_id_seq\', (SELECT MAX(id) FROM wishlist))');
        $this->addSql('ALTER TABLE wishlist ALTER id SET DEFAULT nextval(\'wishlist_id_seq\')');
        $this->addSql('ALTER TABLE wishlist ALTER priority SET DEFAULT 0');
    }
}
