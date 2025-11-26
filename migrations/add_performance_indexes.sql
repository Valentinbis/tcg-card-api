-- Migration pour optimiser les performances avec des index stratégiques
-- Exécuté le: 2025-11-06

-- Index sur user.api_token pour l'authentification (recherches fréquentes)
CREATE INDEX IF NOT EXISTS idx_user_api_token ON "user" (api_token);

-- Index sur user.token_expires_at pour vérifier l'expiration rapidement
CREATE INDEX IF NOT EXISTS idx_user_token_expires ON "user" (token_expires_at);

-- Index sur user.last_activity_at pour les requêtes d'inactivité
CREATE INDEX IF NOT EXISTS idx_user_last_activity ON "user" (last_activity_at);

-- Index sur cards.name pour les recherches de cartes
CREATE INDEX IF NOT EXISTS idx_cards_name ON cards (name);

-- Index sur cards.number pour le tri et les filtres
CREATE INDEX IF NOT EXISTS idx_cards_number ON cards (number);

-- Index composite sur cards pour les filtres fréquents (set, rarity)
CREATE INDEX IF NOT EXISTS idx_cards_set_rarity ON cards (set_id, rarity);

-- Index composite sur cards pour les recherches (name, nameFr)
CREATE INDEX IF NOT EXISTS idx_cards_names ON cards (name, "nameFr");

-- Index sur user_card.user_id pour les requêtes de collection par utilisateur
CREATE INDEX IF NOT EXISTS idx_user_card_user ON user_card (user_id);

-- Index sur user_card.card_id pour les requêtes inverses
CREATE INDEX IF NOT EXISTS idx_user_card_card ON user_card (card_id);

-- Index composite sur collection pour les vérifications de possession
CREATE INDEX IF NOT EXISTS idx_collection_user_card ON collection (user_id, card_id);

-- Index sur set.release_date pour le tri des sets
CREATE INDEX IF NOT EXISTS idx_set_release_date ON "set" (release_date DESC);
