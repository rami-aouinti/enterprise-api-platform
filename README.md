# Plateforme d'intégration d'API d'entreprise

Un projet pratique orienté production pour démontrer la gestion et la gouvernance des API, l'intégration sécurisée de services, l'architecture événementielle et un moteur d'intégration extensible de type iPaaS.

> **Objectif :** construire et expliquer une plateforme réaliste en lien avec un poste de Senior API Developer. Il s'agit d'un projet de portfolio indépendant, et non d'un système de DEVK.

## Éléments actuellement implémentés

- API Customer avec Symfony 7.4 / PHP 8.4.
- PostgreSQL 17 avec volume Docker persistant.
- Keycloak 26.3.3 avec un realm local reproductible `enterprise-platform`.
- OIDC Authorization Code + PKCE pour le client public du portail.
- OAuth 2.0 Client Credentials pour le client machine confidentiel.
- Rôles du realm, audience API, jetons d'accès de cinq minutes et consignes de rotation JWKS.
- Environnement local Docker Compose et contrôles de santé des conteneurs.
- Endpoint de vivacité : `GET /health`.
- Endpoint de disponibilité : `GET /health/ready` (exécute `SELECT 1` sur PostgreSQL).
- Suites de tests unitaires et fonctionnels PHPUnit.
- Analyse statique PHPStan et formatage PHP-CS-Fixer.
- Workflow qualité backend avec GitHub Actions.

## Démarrage local

Prérequis : Git, Docker Engine/Desktop et Docker Compose v2.

~~~bash
cp .env.example .env
# Remplacer chaque valeur fictive du fichier .env par une valeur locale privée.
docker compose config --quiet
docker compose up -d --build
docker compose ps
curl --fail http://localhost:8081/health/ready
sh scripts/keycloak-provision.sh
sh scripts/keycloak-token-smoke-test.sh
~~~

Consulter [Les flux d'identité Keycloak](docs/security/keycloak-identity.md) pour PKCE, Client Credentials, la validation des jetons et la rotation des clés.

Vérifier les endpoints de l'API :

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
bash scripts/smoke-test.sh
~~~

## Commandes de qualité backend

Le conteneur API inclut Composer et les dépendances du projet :

~~~bash
docker compose exec api php bin/console about
docker compose exec api composer test
docker compose exec api composer test:unit
docker compose exec api composer test:functional
docker compose exec api composer analyse
docker compose exec api composer cs:check
~~~

La première résolution des dépendances crée `composer.lock` dans le conteneur. Il faut le copier dans le dépôt et le commiter afin que les builds locaux et la CI utilisent exactement les mêmes versions :

~~~bash
docker compose cp api:/app/composer.lock ./composer.lock
git add composer.lock
git commit -m "chore(deps): lock Symfony backend dependencies (#15)"
git push origin main
~~~

## Mise à jour du code après un `git pull`

Le service API est construit dans une image Docker avec `COPY . .` et aucun bind mount du code source. Après avoir récupéré des modifications backend sur `main`, il faut donc reconstruire et recréer le conteneur API pour qu'il embarque les nouveaux fichiers :

~~~bash
git pull --ff-only origin main
docker compose up -d --build api
docker compose exec -T api php bin/console cache:clear
~~~

Un simple `docker compose restart api` ou `cache:clear` ne met pas à jour le code intégré à l'image.

## Commandes Docker utiles

~~~bash
docker compose logs -f
docker compose logs -f api
docker compose logs -f database
docker compose logs -f keycloak
docker compose down
docker compose up -d
~~~

**Réinitialisation destructive :** cette opération supprime les volumes PostgreSQL et Keycloak ainsi que toutes les données locales qu'ils contiennent.

~~~bash
docker compose down -v
docker compose up -d --build
~~~

L'API est exposée sur 127.0.0.1:8000, PostgreSQL sur 127.0.0.1:5432 et Keycloak sur 127.0.0.1:8081. Depuis le conteneur API, la base est accessible à l'adresse `database:5432`. Utiliser uniquement des données synthétiques.

## Socle technologique cible

| Domaine | Choix initial |
|---|---|
| Services backend | PHP 8.4, Symfony 7.4 |
| Portail d'administration | React, TypeScript, Vite |
| API Gateway | Kong Gateway (OSS) |
| Identité | Keycloak (OAuth 2.0 / OIDC) |
| Contrats API | OpenAPI 3.1 |
| Linting/gouvernance API | Spectral |
| Base relationnelle | PostgreSQL |
| Messagerie | RabbitMQ |
| Streaming d'événements | Apache Kafka |
| Cache | Redis, uniquement si un besoin le justifie |
| Observabilité | OpenTelemetry, Prometheus, Grafana, Loki, Jaeger |
| Environnement local | Docker Compose |
| Livraison continue | GitHub Actions |

Il s'agit d'une architecture de référence pratique, et non d'une obligation liée à un fournisseur. Les versions et décisions de conception sont consignées dans les [ADR](docs/adr/README.md).

## Architecture en bref

Portail React Développeur/Administration → Kong Gateway → API métier Symfony

Composants complémentaires :
- Keycloak gère les identités humaines et machines et émet les jetons OAuth2/OIDC.
- PostgreSQL stocke les données transactionnelles appartenant à chaque service.
- RabbitMQ gère les traitements asynchrones orientés tâches.
- Kafka transporte les flux d'événements métier durables lorsque le rejeu ou plusieurs consommateurs sont nécessaires.
- Le moteur d'intégration connecte les systèmes via des adaptateurs, transformations, règles de routage et exécutions.
- OpenTelemetry instrumente les services ; Prometheus/Grafana, Loki et Jaeger assurent l'exploitation des métriques, journaux et traces.

Voir [Vue d'ensemble de l'architecture](docs/architecture/overview.md), [Choix technologiques](docs/architecture/technology-decisions.md) et [Feuille de route](docs/roadmap/README.md).

## Documentation

- [Guide du projet](docs/PROJECT-HANDBOOK.md)
- [Guide de contribution](CONTRIBUTING.md) — workflow direct sur main.
- [Workflow de développement](docs/development/workflow.md)
- [Installation locale](docs/development/local-setup.md)
- [Structure du dépôt](docs/development/repository-structure.md)
- [Vue d'ensemble de l'architecture](docs/architecture/overview.md)
- [Frontières des services](docs/architecture/service-boundaries.md)
- [Flux d'identité Keycloak](docs/security/keycloak-identity.md)
- [Choix technologiques](docs/architecture/technology-decisions.md)
- [Registre des décisions d'architecture](docs/adr/README.md)
- [Gouvernance des API](docs/api-governance/standards.md)
- [Modèle de menaces de sécurité](docs/security/threat-model.md)
- [Conception iPaaS](docs/integration/ipaas-design.md)
- [Observabilité](docs/operations/observability.md)
- [Feuille de route](docs/roadmap/README.md)

## Méthode de livraison

Nous travaillons directement sur `main` pour ce dépôt. Ne créer ni branche ni pull request. Traiter un ticket à la fois, utiliser des messages Conventional Commits ciblés avec le numéro du ticket, valider les changements et documenter les résultats réellement obtenus.

## Structure cible du dépôt

~~~text
.
├── services/
│   ├── customer-service/
│   ├── policy-service/
│   ├── claims-service/
│   └── billing-service/
├── apps/
│   ├── api-gateway-config/
│   ├── developer-portal/
│   └── integration-runtime/
├── packages/shared-contracts/
├── infrastructure/
│   ├── docker/
│   ├── keycloak/
│   ├── kong/
│   ├── observability/
│   └── kubernetes/
└── docs/
~~~

Cette arborescence est une cible : elle ne signifie pas que tous les composants listés existent déjà.

## État du projet

**Phase 01 — Socle d'exécution :** Docker Compose et le squelette backend Symfony sont committés. La Phase 02 définit les premières frontières métier et l'API Customer. La Phase 03 introduit l'identité et l'application des règles de sécurité au niveau de la gateway.

## Objectifs d'apprentissage

Le projet doit fournir du code et des tests concrets pour expliquer la conception et la gouvernance des API, les responsabilités d'une gateway, OAuth2/OIDC, la protection contre les menaces, l'intégration synchrone et asynchrone, les garanties de livraison, la conception de connecteurs iPaaS, l'observabilité et les compromis opérationnels.
