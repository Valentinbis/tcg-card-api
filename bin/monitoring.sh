#!/bin/bash

# Script de gestion de la stack de monitoring TCG Card API
# Usage: ./bin/monitoring.sh [start|stop|restart|status|logs|clean]

set -e

COMPOSE_FILE="compose.monitoring.yaml"
PROJECT_NAME="tcgcard_monitoring"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonctions helper
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Fonction : Démarrer la stack
start_monitoring() {
    log_info "Démarrage de la stack de monitoring..."
    
    docker-compose -f "$COMPOSE_FILE" up -d
    
    log_success "Stack de monitoring démarrée !"
    echo ""
    echo "📊 Accès aux services :"
    echo "  - Grafana:     http://localhost:3000 (admin/admin)"
    echo "  - Prometheus:  http://localhost:9090"
    echo "  - Loki:        http://localhost:3100"
    echo "  - cAdvisor:    http://localhost:8080"
    echo ""
    log_info "Les dashboards se chargent automatiquement dans Grafana"
}

# Fonction : Stopper la stack
stop_monitoring() {
    log_info "Arrêt de la stack de monitoring..."
    docker-compose -f "$COMPOSE_FILE" down
    log_success "Stack de monitoring arrêtée"
}

# Fonction : Redémarrer la stack
restart_monitoring() {
    log_info "Redémarrage de la stack de monitoring..."
    docker-compose -f "$COMPOSE_FILE" restart
    log_success "Stack de monitoring redémarrée"
}

# Fonction : Statut des services
status_monitoring() {
    log_info "Statut de la stack de monitoring :"
    echo ""
    docker-compose -f "$COMPOSE_FILE" ps
    echo ""
    
    # Vérifier l'état de santé
    log_info "Vérification de santé :"
    
    # Grafana
    if curl -s http://localhost:3000/api/health > /dev/null 2>&1; then
        log_success "Grafana est accessible"
    else
        log_error "Grafana n'est pas accessible"
    fi
    
    # Loki
    if curl -s http://localhost:3100/ready > /dev/null 2>&1; then
        log_success "Loki est prêt"
    else
        log_error "Loki n'est pas prêt"
    fi
    
    # Prometheus
    if curl -s http://localhost:9090/-/healthy > /dev/null 2>&1; then
        log_success "Prometheus est en bonne santé"
    else
        log_error "Prometheus n'est pas en bonne santé"
    fi
}

# Fonction : Voir les logs
logs_monitoring() {
    SERVICE="${2:-}"
    
    if [ -z "$SERVICE" ]; then
        log_info "Affichage des logs de tous les services (Ctrl+C pour quitter)..."
        docker-compose -f "$COMPOSE_FILE" logs -f
    else
        log_info "Affichage des logs de $SERVICE (Ctrl+C pour quitter)..."
        docker-compose -f "$COMPOSE_FILE" logs -f "$SERVICE"
    fi
}

# Fonction : Nettoyer les données
clean_monitoring() {
    log_warning "⚠️  ATTENTION : Cette action va supprimer TOUTES les données de monitoring !"
    read -p "Êtes-vous sûr de vouloir continuer ? (yes/no) " -n 3 -r
    echo
    
    if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        log_info "Annulation du nettoyage"
        exit 0
    fi
    
    log_info "Arrêt de la stack..."
    docker-compose -f "$COMPOSE_FILE" down -v
    
    log_info "Suppression des volumes..."
    docker volume rm \
        api_grafana_data \
        api_loki_data \
        api_prometheus_data 2>/dev/null || true
    
    log_success "Nettoyage terminé"
    log_info "Vous pouvez redémarrer avec: $0 start"
}

# Fonction : Afficher l'aide
show_help() {
    echo "Usage: $0 [command] [options]"
    echo ""
    echo "Commandes disponibles :"
    echo "  start      - Démarrer la stack de monitoring"
    echo "  stop       - Arrêter la stack de monitoring"
    echo "  restart    - Redémarrer la stack de monitoring"
    echo "  status     - Afficher le statut des services"
    echo "  logs       - Afficher les logs (optionnel: nom du service)"
    echo "  clean      - Nettoyer toutes les données (⚠️ destructif)"
    echo "  help       - Afficher cette aide"
    echo ""
    echo "Exemples :"
    echo "  $0 start"
    echo "  $0 logs grafana"
    echo "  $0 status"
}

# Point d'entrée du script
main() {
    COMMAND="${1:-help}"
    
    case "$COMMAND" in
        start)
            start_monitoring
            ;;
        stop)
            stop_monitoring
            ;;
        restart)
            restart_monitoring
            ;;
        status)
            status_monitoring
            ;;
        logs)
            logs_monitoring "$@"
            ;;
        clean)
            clean_monitoring
            ;;
        help|--help|-h)
            show_help
            ;;
        *)
            log_error "Commande inconnue: $COMMAND"
            echo ""
            show_help
            exit 1
            ;;
    esac
}

# Exécuter le script
main "$@"
