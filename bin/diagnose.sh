#!/bin/bash

# Script de diagnostic TCG Card API
# Usage: ./bin/diagnose.sh

set -e

echo "🔍 Diagnostic TCG Card API"
echo "=========================="
echo ""

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_success() {
    echo -e "${GREEN}✓${NC} $1"
}

check_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

check_error() {
    echo -e "${RED}✗${NC} $1"
}

# 1. Vérification de Docker
echo "📦 Docker"
echo "----------"
if command -v docker &> /dev/null; then
    DOCKER_VERSION=$(docker --version)
    check_success "Docker installé: $DOCKER_VERSION"
else
    check_error "Docker n'est pas installé"
    exit 1
fi

if command -v docker-compose &> /dev/null || docker compose version &> /dev/null; then
    COMPOSE_VERSION=$(docker compose version 2>/dev/null || docker-compose --version)
    check_success "Docker Compose installé: $COMPOSE_VERSION"
else
    check_error "Docker Compose n'est pas installé"
    exit 1
fi
echo ""

# 2. Vérification des containers
echo "🐳 Containers Docker"
echo "--------------------"
if docker ps | grep -q tcgcard_api; then
    check_success "Container tcgcard_api en cours d'exécution"
else
    check_error "Container tcgcard_api n'est pas démarré"
    echo "  → Lancez: docker compose up -d"
fi

if docker ps | grep -q tcgcard_db; then
    check_success "Container tcgcard_db en cours d'exécution"
else
    check_error "Container tcgcard_db n'est pas démarré"
fi
echo ""

# 3. Vérification PHP
echo "🐘 PHP"
echo "------"
if docker exec tcgcard_api php --version &> /dev/null; then
    PHP_VERSION=$(docker exec tcgcard_api php --version | head -n 1)
    check_success "$PHP_VERSION"
    
    # Extensions PHP
    REQUIRED_EXTENSIONS=("pdo_pgsql" "intl" "mbstring" "xml" "curl" "opcache")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if docker exec tcgcard_api php -m | grep -q "$ext"; then
            check_success "Extension $ext installée"
        else
            check_error "Extension $ext manquante"
        fi
    done
else
    check_error "Impossible de vérifier la version de PHP"
fi
echo ""

# 4. Vérification Composer
echo "📚 Composer"
echo "-----------"
if docker exec tcgcard_api composer --version &> /dev/null; then
    COMPOSER_VERSION=$(docker exec tcgcard_api composer --version)
    check_success "$COMPOSER_VERSION"
    
    # Vérifier si vendor existe
    if docker exec tcgcard_api test -d vendor; then
        check_success "Dépendances installées (vendor/)"
    else
        check_warning "Dépendances non installées"
        echo "  → Lancez: docker exec -it tcgcard_api composer install"
    fi
else
    check_error "Composer non accessible"
fi
echo ""

# 5. Vérification de la base de données
echo "🗄️  Base de données"
echo "------------------"
if docker exec tcgcard_db pg_isready -U tcgcard &> /dev/null; then
    check_success "PostgreSQL accessible"
    
    # Compter les tables
    TABLE_COUNT=$(docker exec tcgcard_db psql -U tcgcard -d tcgcard -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" 2>/dev/null || echo "0")
    if [ "$TABLE_COUNT" -gt 0 ]; then
        check_success "$TABLE_COUNT tables dans la base de données"
    else
        check_warning "Aucune table dans la base de données"
        echo "  → Vérifiez que l'initialisation s'est bien déroulée"
    fi
else
    check_error "PostgreSQL non accessible"
fi
echo ""

# 6. Vérification de l'API
echo "🌐 API"
echo "------"
API_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api 2>/dev/null || echo "000")
if [ "$API_RESPONSE" = "200" ]; then
    check_success "API accessible (HTTP $API_RESPONSE)"
    
    # Vérifier le contenu
    API_CONTENT=$(curl -s http://localhost:8000/api)
    if echo "$API_CONTENT" | grep -q "Tcgcard"; then
        check_success "API répond correctement"
    else
        check_warning "API répond mais le contenu semble incorrect"
    fi
else
    check_error "API non accessible (HTTP $API_RESPONSE)"
    echo "  → Vérifiez les logs: docker compose logs tcgcard_api"
fi
echo ""

# 7. Vérification des fichiers de configuration
echo "⚙️  Configuration"
echo "----------------"
if [ -f .env ]; then
    check_success ".env présent"
else
    check_error ".env manquant"
fi

if [ -f .env.local ]; then
    check_success ".env.local présent"
else
    check_warning ".env.local non trouvé (utilise .env par défaut)"
fi

if [ -f composer.json ]; then
    check_success "composer.json présent"
else
    check_error "composer.json manquant"
fi
echo ""

# 8. Vérification des permissions
echo "🔐 Permissions"
echo "--------------"
VAR_WRITABLE=$(docker exec tcgcard_api test -w /app/var && echo "yes" || echo "no")
if [ "$VAR_WRITABLE" = "yes" ]; then
    check_success "Répertoire var/ accessible en écriture"
else
    check_error "Répertoire var/ non accessible en écriture"
    echo "  → Lancez: docker exec tcgcard_api chmod -R 777 var/"
fi
echo ""

# 9. Vérification des logs
echo "📋 Logs récents"
echo "---------------"
if docker exec tcgcard_api test -f var/log/dev.log; then
    ERROR_COUNT=$(docker exec tcgcard_api grep -c "ERROR" var/log/dev.log 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        check_warning "$ERROR_COUNT erreurs trouvées dans var/log/dev.log"
        echo "  → Voir les erreurs: docker exec tcgcard_api tail -n 20 var/log/dev.log"
    else
        check_success "Aucune erreur récente dans les logs"
    fi
else
    check_warning "Fichier de log non trouvé"
fi
echo ""

# 10. Résumé
echo "📊 Résumé"
echo "---------"
echo "Pour plus d'informations:"
echo "  • Logs API: docker compose logs -f tcgcard_api"
echo "  • Logs DB: docker compose logs -f tcgcard_db"
echo "  • Tests: docker exec -it tcgcard_api php bin/phpunit"
echo "  • Documentation: http://localhost:8000/api/documentation"
echo ""
echo "✅ Diagnostic terminé !"
