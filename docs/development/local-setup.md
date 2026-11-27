# Installation et environnement de développement local

## Prérequis

- Git 2.4x ou version plus récente.
- Docker Engine ou Docker Desktop.
- Docker Compose v2.
- Un terminal capable d'exécuter des commandes shell.
- `curl`, `jq` et `openssl` pour les tests rapides des flux d'identité.

PHP et Composer installés sur la machine hôte sont facultatifs : le conteneur API fournit PHP 8.4 et Composer.

Vérifier les outils nécessaires :

~~~bash
git --version
docker --version
docker compose version
~~~

## Premier démarrage

Depuis la racine du dépôt :

~~~bash
cp .env.example .env
~~~

Définir des valeurs locales privées pour `POSTGRES_PASSWORD`, `APP_SECRET`,
`KEYCLOAK_ADMIN_PASSWORD` et `KEYCLOAK_MACHINE_CLIENT_SECRET` dans `.env`.
Utiliser des valeurs aléatoires longues et ne jamais commiter ce fichier.

Valider et démarrer :

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

La stack contient Symfony 7.4 / PHP 8.4, PostgreSQL 17 et Keycloak 26.3.3.
Les données PostgreSQL et Keycloak sont stockées dans des volumes persistants
distincts. L'API se connecte à PostgreSQL via le réseau privé Compose. Keycloak
est disponible sur `http://localhost:8081` ; ses identifiants administrateur
locaux proviennent du fichier `.env` ignoré par Git.

Provisionner le client machine confidentiel lorsque Keycloak est prêt :

~~~bash
curl --fail http://localhost:9000/health/ready
bash scripts/keycloak-provision.sh
~~~

Voir [Flux d'identité Keycloak](../security/keycloak-identity.md) pour le flux
navigateur PKCE, Client Credentials, les claims JWT, la validation JWKS et la
procédure de rotation des clés.

## Contrôles de santé

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
curl --fail http://localhost:9000/health/ready
bash scripts/smoke-test.sh
bash scripts/keycloak-token-smoke-test.sh
docker compose ps
~~~

- `/health` confirme que l'application HTTP Symfony répond.
- `/health/ready` exécute une requête légère sur la base et renvoie HTTP 503 si PostgreSQL est indisponible.
- L'endpoint de gestion Keycloak sur le port `9000` confirme que le fournisseur d'identité est prêt. Le port `8081` sert aux endpoints HTTP du realm (OIDC, autorisation et jetons).
- Le test rapide de jeton vérifie la découverte OIDC et obtient un jeton machine.

## Commandes backend

~~~bash
# Vue d'ensemble de l'environnement et de la configuration Symfony
docker compose exec api php bin/console about

# Tests
docker compose exec api composer test
docker compose exec api composer test:unit
docker compose exec api composer test:functional

# Analyse statique et formatage
docker compose exec api composer analyse
docker compose exec api composer cs:check
~~~

La première résolution des dépendances crée `composer.lock` dans le conteneur. Le copier et le commiter permet de rendre les builds et la CI reproductibles :

~~~bash
docker compose cp api:/app/composer.lock ./composer.lock
git add composer.lock
git commit -m "chore(deps): lock Symfony backend dependencies (#15)"
git push origin main
~~~

## Journaux et cycle de vie

~~~bash
docker compose logs -f
docker compose logs -f api
docker compose logs -f database
docker compose logs -f keycloak

# Arrêter les conteneurs en conservant les données de la base et de l'identité
docker compose down

# Redémarrer
docker compose up -d
~~~

## Réinitialisation des données locales

**Attention :** cette opération supprime définitivement les volumes PostgreSQL et Keycloak ainsi que toutes les données qu'ils contiennent.

~~~bash
docker compose down -v
docker compose up -d --build
~~~

## Fichiers d'environnement et secrets

- Commiter uniquement des exemples sûrs comme `.env.example`, contenant des valeurs fictives.
- Conserver les vraies valeurs locales dans `.env` ou `.env.local`, ignorés par Git.
- Ne jamais commiter de mots de passe, jetons, clés privées, certificats, chaînes de connexion de production ou données personnelles/clients.
- Préférer l'injection de variables d'environnement ou un gestionnaire de secrets à l'intégration de credentials dans les fichiers Docker Compose.
- Faire tourner tout identifiant accidentellement exposé ; le supprimer du dernier commit ne suffit pas.
- Utiliser uniquement des données synthétiques en développement. Ne jamais importer de vraies données clients ou exports de production.

## Dépannage

- Si Compose signale une variable manquante, copier `.env.example` vers `.env` et définir toutes les valeurs locales obligatoires.
- Si le conteneur API ne se construit pas, examiner la sortie de `docker compose build --no-cache api`.
- Si l'API reste non saine, examiner `docker compose logs api database`.
- Si Keycloak ne devient pas prêt, examiner `docker compose logs keycloak` et vérifier que les ports 8081 et 9000 sont disponibles.
- Si le port 8000, 5432 ou 8081 est déjà utilisé, arrêter le service qui entre en conflit.
- Pour recréer les conteneurs sans supprimer les données, exécuter `docker compose down`, puis `docker compose up -d --build`.
