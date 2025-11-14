<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour créer la table wishlist
 */
final class Version20251114093700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create wishlist table for user card wishlists';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE wishlist (
            id SERIAL PRIMARY KEY,
            user_id INT NOT NULL,
            card_id VARCHAR(50) NOT NULL,
            priority INT NOT NULL DEFAULT 0,
            notes TEXT DEFAULT NULL,
            max_price NUMERIC(10, 2) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
        )');

        $this->addSql('CREATE INDEX idx_wishlist_user ON wishlist (user_id)');
        $this->addSql('CREATE INDEX idx_wishlist_card ON wishlist (card_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_card ON wishlist (user_id, card_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE wishlist');
    }
}
