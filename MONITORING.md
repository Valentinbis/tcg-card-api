# 📊 Stack de Monitoring TCG Card API

## 🎯 Vue d'ensemble

Stack de monitoring légère et complète basée sur :
- **Grafana** : Visualisation et dashboards
- **Loki** : Agrégation de logs
- **Promtail** : Collecte de logs
- **Prometheus** : Métriques système
- **Node Exporter** : Métriques serveur (CPU, RAM, Disk)
- **cAdvisor** : Métriques containers Docker

## 🚀 Démarrage rapide

### 1. Démarrer la stack de monitoring

```bash
# Démarrer tous les services de monitoring
docker-compose -f compose.monitoring.yaml up -d

# Vérifier que tout est démarré
docker-compose -f compose.monitoring.yaml ps
```

### 2. Accéder aux interfaces

| Service | URL | Identifiants par défaut |
|---------|-----|-------------------------|
| **Grafana** | http://localhost:3000 | admin / admin |
| **Prometheus** | http://localhost:9090 | - |
| **Loki** | http://localhost:3100 | - |
| **cAdvisor** | http://localhost:8081 | - |

### 3. Première connexion Grafana

1. Ouvrir http://localhost:3000
2. Se connecter avec `admin` / `admin`
3. Changer le mot de passe (recommandé)
4. Les datasources et dashboards sont automatiquement configurés ✅

## 📈 Dashboards disponibles

### 1. **TCG Card API - Vue d'ensemble**
Monitoring complet de l'API Symfony :
- ✅ Requêtes par seconde (RPS)
- ✅ Temps de réponse moyen (5min)
- ✅ Taux d'erreurs 5xx
- ✅ Répartition des codes HTTP
- ✅ Percentiles de temps de réponse (p50, p95, p99)
- ✅ Logs en temps réel
- ✅ Répartition par niveau de log
- ✅ Top 10 des endpoints les plus lents

### 2. **TCG Card - Infrastructure Système**
Métriques système et Docker :
- ✅ Utilisation CPU
- ✅ Utilisation Mémoire
- ✅ Utilisation Disque
- ✅ Réseau (Entrée/Sortie)
- ✅ Mémoire des containers Docker
- ✅ CPU des containers Docker

## 🔔 Alertes configurées

Les alertes suivantes sont actives :

| Alerte | Seuil | Durée | Sévérité |
|--------|-------|-------|----------|
| ⚠️ Temps de réponse lent | > 2s | 5min | Warning |
| 🔥 Taux d'erreurs élevé | > 5% | 2min | Critical |
| ❌ Pic de logs ERROR | > 10/min | 3min | Warning |
| 🧠 Mémoire container élevée | > 80% | 5min | Warning |
| 💻 CPU container élevé | > 90% | 5min | Critical |
| 💾 Espace disque faible | > 85% | 10min | Warning |

### Configuration des notifications

Modifier le fichier `monitoring/grafana/provisioning/alerting/alerts.yaml` :

```yaml
contactPoints:
  - orgId: 1
    name: Default Email
    receivers:
      - uid: default-email
        type: email
        settings:
          addresses: "votre-email@example.com"  # ← Changer ici
```

## 🔍 Requêtes LogQL utiles

### Filtrer les logs par niveau
```logql
{job="symfony_requests"} | json | level="ERROR"
```

### Requêtes lentes (> 1s)
```logql
{job="symfony_performance"} | json | unwrap duration_ms | duration_ms > 1000
```

### Logs d'un endpoint spécifique
```logql
{job="symfony_requests"} | json | uri="/api/login"
```

### Erreurs 5xx
```logql
{job="symfony_requests"} | json | status=~"5.."
```

### Actions d'un utilisateur spécifique
```logql
{job="symfony_actions"} | json | user_id="104"
```

## 📊 Requêtes PromQL utiles

### Top 5 containers par mémoire
```promql
topk(5, container_memory_usage_bytes{name=~"tcgcard.*"})
```

### Charge CPU moyenne sur 5min
```promql
avg(rate(node_cpu_seconds_total{mode!="idle"}[5m]))
```

### Réseau total entrant
```promql
sum(rate(node_network_receive_bytes_total[5m]))
```

## 🛠️ Configuration avancée

### Modifier la rétention des logs (Loki)

Éditer `monitoring/loki/loki-config.yaml` :

```yaml
limits_config:
  retention_period: 30d  # ← Changer ici (7d, 15d, 30d, 90d)
```

### Ajouter un nouveau job Promtail

Éditer `monitoring/promtail/promtail-config.yaml` :

```yaml
scrape_configs:
  - job_name: mon_nouveau_job
    static_configs:
      - targets:
          - localhost
        labels:
          job: mon_job
          __path__: /var/log/app/mon_fichier*.log
```

### Ajouter une métrique Prometheus

Éditer `monitoring/prometheus/prometheus.yaml` :

```yaml
scrape_configs:
  - job_name: 'mon_service'
    static_configs:
      - targets: ['mon_service:9090']
```

## 📦 Gestion des volumes

### Voir l'espace utilisé
```bash
docker system df -v | grep monitoring
```

### Nettoyer les anciennes données
```bash
# Stopper la stack
docker-compose -f compose.monitoring.yaml down

# Supprimer les volumes (attention : perte de données !)
docker volume rm \
  api_grafana_data \
  api_loki_data \
  api_prometheus_data

# Redémarrer
docker-compose -f compose.monitoring.yaml up -d
```

## 🔧 Troubleshooting

### Grafana ne se connecte pas à Loki
```bash
# Vérifier que Loki est accessible
docker exec -it tcgcard_grafana curl http://loki:3100/ready

# Vérifier les logs Loki
docker logs tcgcard_loki
```

### Promtail ne collecte pas de logs
```bash
# Vérifier que les logs sont accessibles
docker exec -it tcgcard_promtail ls -la /var/log/app/

# Vérifier les logs Promtail
docker logs tcgcard_promtail
```

### Dashboards vides
```bash
# Vérifier que l'API génère des logs
tail -f var/log/app-$(date +%Y-%m-%d).json

# Vérifier que Promtail envoie à Loki
docker logs tcgcard_promtail | grep "batch"

# Requête de test dans Grafana Explore
{job="symfony_requests"}
```

## 🎓 Ressources

- [Documentation Grafana](https://grafana.com/docs/grafana/latest/)
- [Documentation Loki](https://grafana.com/docs/loki/latest/)
- [LogQL Cheat Sheet](https://grafana.com/docs/loki/latest/logql/)
- [PromQL Cheat Sheet](https://promlabs.com/promql-cheat-sheet/)

## 📝 Variables d'environnement

Créer un fichier `.env` à la racine :

```env
# Grafana
GRAFANA_ADMIN_USER=admin
GRAFANA_ADMIN_PASSWORD=votre_mot_de_passe_securise

# Optionnel : Configuration des alertes email
# GF_SMTP_ENABLED=true
# GF_SMTP_HOST=smtp.gmail.com:587
# GF_SMTP_USER=votre@email.com
# GF_SMTP_PASSWORD=votre_password
```

## 🔒 Sécurité

### En production

1. **Changer le mot de passe Grafana par défaut**
2. **Activer HTTPS** (reverse proxy nginx/traefik)
3. **Limiter l'accès réseau** (firewall, VPN)
4. **Activer l'authentification Prometheus** si exposé
5. **Configurer la rétention** pour éviter de saturer le disque

### Recommandations

```yaml
# Ne PAS exposer publiquement ces ports :
# - 3000 (Grafana) → Mettre derrière un reverse proxy
# - 3100 (Loki) → Accès interne uniquement
# - 9090 (Prometheus) → Accès interne uniquement
# - 8080 (cAdvisor) → Accès interne uniquement
```

## 🚦 Statut des services

Vérifier rapidement l'état :

```bash
# Script de vérification rapide
docker-compose -f compose.monitoring.yaml ps --format "table {{.Name}}\t{{.Status}}\t{{.Ports}}"
```

Sortie attendue :
```
NAME                    STATUS         PORTS
tcgcard_grafana         Up X minutes   0.0.0.0:3000->3000/tcp
tcgcard_loki            Up X minutes   0.0.0.0:3100->3100/tcp
tcgcard_promtail        Up X minutes   
tcgcard_prometheus      Up X minutes   0.0.0.0:9090->9090/tcp
tcgcard_node_exporter   Up X minutes   
tcgcard_cadvisor        Up X minutes   0.0.0.0:8080->8080/tcp
```

## 🎯 Prochaines étapes

1. ✅ Personnaliser les dashboards selon vos besoins
2. ✅ Configurer les notifications par email/Slack
3. ✅ Ajuster les seuils d'alertes
4. ✅ Créer des dashboards métier spécifiques
5. ✅ Activer le monitoring applicatif Symfony (optionnel)
