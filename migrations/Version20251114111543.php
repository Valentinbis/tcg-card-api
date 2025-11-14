<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114111543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add languages field to collection table and migrate data from user_card';
    }

    public function up(Schema $schema): void
    {
        // 1. Ajouter le champ languages
        $this->addSql('ALTER TABLE collection ADD languages JSON DEFAULT NULL');
        
        // 2. Migrer les données de user_card vers collection
        $this->addSql("
            INSERT INTO collection (user_id, card_id, quantity, languages, created_at, updated_at)
            SELECT 
                user_id, 
                CAST(card_id AS VARCHAR(50)), 
                1, 
                languages, 
                NOW(), 
                NOW()
            FROM user_card
            ON CONFLICT (user_id, card_id) DO NOTHING
        ");
        
        // 3. Supprimer l'ancienne table user_card
        $this->addSql('DROP TABLE user_card');
    }

    public function down(Schema $schema): void
    {
        // Recréer user_card
        $this->addSql('CREATE TABLE user_card (user_id INT NOT NULL, card_id INT NOT NULL, languages JSON DEFAULT NULL, PRIMARY KEY(user_id, card_id))');
        
        // Migrer les données de collection vers user_card
        $this->addSql("
            INSERT INTO user_card (user_id, card_id, languages)
            SELECT user_id, CAST(card_id AS INTEGER), languages
            FROM collection
            WHERE languages IS NOT NULL
        ");
        
        // Supprimer le champ languages de collection
        $this->addSql('ALTER TABLE collection DROP languages');
    }
}
