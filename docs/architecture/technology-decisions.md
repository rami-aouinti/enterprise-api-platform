# Choix technologiques et compromis

Ce document consigne le socle initial. Les versions précises et les modes de déploiement sont finalisés dans les tickets d'implémentation et les ADR.

| Technologie | Responsabilité | Pourquoi ce choix | Compromis / précautions |
|---|---|---|---|
| PHP 8.4 + Symfony 7 | API métier et moteur d'intégration | Correspond à l'expérience backend ; écosystème mature HTTP, DI, Messenger, validation et sécurité | Garder les services déployables indépendamment ; éviter le couplage par un noyau partagé |
| React + TypeScript | Portail opérateur | Écosystème riche et interface typée | Ne pas prendre les décisions d'autorisation uniquement dans le navigateur |
| Kong Gateway | Périphérie API | Routage déclaratif et écosystème de plugins | Les fonctions OSS diffèrent de l'édition entreprise ; valider l'approche du plugin OIDC |
| Keycloak | Fournisseur d'identité | OAuth2/OIDC standard et apprentissage auto-hébergé | La configuration des realms/clients et la rotation des clés nécessitent une attention opérationnelle |
| OpenAPI 3.1 | Contrats API | Format de contrat portable et compatible avec de nombreux outils | Garder le contrat aligné sur le comportement réel |
| Spectral | Linting API | Règles de gouvernance personnalisables | Les règles doivent être utiles et actionnables, pas du bruit stylistique |
| PostgreSQL | Données transactionnelles | Persistance relationnelle fiable | Chaque service possède ses frontières de schéma et de données |
| RabbitMQ | Files de travail | Routage, accusés de réception, retries et DLQ | Ne pas considérer la livraison comme « exactement une fois » |
| Kafka | Flux d'événements | Rétention, rejeu, groupes de consommateurs | Complexité opérationnelle ; l'utiliser uniquement si la sémantique de flux apporte une valeur |
| Redis | Cache/coordination | Cache partagé rapide | L'invalidation du cache et sa durabilité doivent être explicites |
| OpenTelemetry | Instrumentation | Télémétrie indépendante des fournisseurs | Cardinalité, échantillonnage et données sensibles nécessitent des contrôles |
| Prometheus/Grafana/Loki/Jaeger | Exploitation | Stack pratique de métriques/journaux/traces | Limiter consommation des ressources et rétention |
| Docker Compose | Orchestration locale | Environnement développeur reproductible | N'est pas un orchestrateur de production |
| GitHub Actions | CI | Automatisation native du dépôt | Épingler les versions des actions et appliquer le moindre privilège |

## Choix de conception assumés

### RabbitMQ et Kafka

Utiliser RabbitMQ pour la distribution de tâches et le traitement de commandes/jobs avec accusés de réception, retries limités et DLQ. Utiliser Kafka pour les flux durables d'événements métier lorsque plusieurs consommateurs indépendants et le rejeu ont du sens. Ne pas dupliquer chaque événement dans les deux brokers sans besoin métier documenté.

### Gateway et sécurité des services

La gateway applique les contrôles en périphérie, mais les services restent responsables de l'autorisation au niveau des ressources et des invariants métier. Un JWT valide ne donne pas automatiquement accès à tous les objets.

### Périmètre iPaaS

Construire un moteur d'intégration pédagogique ciblé plutôt que tenter de recréer un produit iPaaS commercial. Commencer avec peu de types de connecteurs, des mappings explicites, des workflows limités et un historique d'exécution auditable.

### Versionnement API

Privilégier les changements additifs et rétrocompatibles au sein d'une version majeure. Introduire une nouvelle version majeure uniquement lorsque la compatibilité ne peut être préservée. Publier les règles de dépréciation et les dates de retrait.
