<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour optimiser les performances avec des index stratégiques
 */
final class Version20251106100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'index sur les colonnes fréquemment utilisées pour optimiser les performances';
    }

    public function up(Schema $schema): void
    {
        // Index sur user.api_token pour l'authentification
        $this->addSql('CREATE INDEX idx_user_api_token ON "user" (api_token)');
        
        // Index sur user.email (déjà unique mais ajout explicite pour les recherches)
        // UNIQ_IDENTIFIER_EMAIL existe déjà, pas besoin d'en créer un nouveau
        
        // Index sur user.token_expires_at pour vérifier l'expiration rapidement
        $this->addSql('CREATE INDEX idx_user_token_expires ON "user" (token_expires_at)');
        
        // Index sur user.last_activity_at pour les requêtes d'inactivité
        $this->addSql('CREATE INDEX idx_user_last_activity ON "user" (last_activity_at)');
        
        // Index sur cards.name pour les recherches de cartes
        $this->addSql('CREATE INDEX idx_cards_name ON cards (name)');
        
        // Index sur cards.number pour le tri et les filtres
        $this->addSql('CREATE INDEX idx_cards_number ON cards (number)');
        
        // Index composite sur user_card pour les requêtes de collection
        $this->addSql('CREATE INDEX idx_user_card_user ON user_card (user_id)');
        $this->addSql('CREATE INDEX idx_user_card_card ON user_card (card_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_user_api_token');
        $this->addSql('DROP INDEX idx_user_token_expires');
        $this->addSql('DROP INDEX idx_user_last_activity');
        $this->addSql('DROP INDEX idx_cards_name');
        $this->addSql('DROP INDEX idx_cards_number');
        $this->addSql('DROP INDEX idx_user_card_user');
        $this->addSql('DROP INDEX idx_user_card_card');
    }
}
