# Keycloak : identité, jetons et protection de l'API

## Fournisseur d'identité local

Docker Compose démarre Keycloak sur `http://localhost:8081` et importe le
realm `enterprise-platform` depuis
`infrastructure/keycloak/realm/enterprise-platform-realm.json`.

| Composant | Objectif |
|---|---|
| `enterprise-portal` | Client public React/navigateur utilisant Authorization Code + PKCE (S256) |
| `enterprise-api` | Audience attendue par le serveur de ressources |
| `enterprise-api-machine` | Client machine-à-machine confidentiel utilisant Client Credentials |
| `portal-demo` | Utilisateur synthétique pour les tests interactifs |
| `portal-user`, `api.read`, `api.write`, `platform-admin` | Rôles du realm |
| `enterprise-api-audience` | Ajoute `enterprise-api` à l'audience du jeton d'accès |

Le mot de passe de l'utilisateur de démonstration local est `local-demo-only`.
Ne jamais le réutiliser en dehors de ce realm de développement jetable.

## Architecture de sécurité

- **Keycloak** authentifie l'utilisateur ou le client machine et signe le jeton d'accès.
- **Kong Community** reste le point d'entrée HTTP : routage, limitation de débit,
  taille maximale des requêtes et identifiant de corrélation.
- **Symfony** valide le jeton OIDC/JWT et applique les rôles nécessaires avant
  d'exécuter un contrôleur.
- **PostgreSQL** ne reçoit que les opérations autorisées par l'application.

Kong Community ne valide pas ici la signature JWT. Cette responsabilité est
assurée par le composant OIDC natif de Symfony, qui récupère les clés publiques
depuis le document de découverte et le JWKS de Keycloak.

## Configuration issuer et réseau Docker

L'issuer public du realm est :

`http://localhost:8081/realms/enterprise-platform`

L'API tourne dans un conteneur : elle ne peut donc pas utiliser son propre
`localhost:8081` pour joindre Keycloak. Elle utilise l'URL interne :

`http://keycloak:8080/realms/enterprise-platform/`

Keycloak est configuré avec `KC_HOSTNAME` et
`KC_HOSTNAME_BACKCHANNEL_DYNAMIC=true`. Le hostname public reste celui des
jetons et du navigateur, tandis que les requêtes backchannel effectuées depuis
le réseau Docker peuvent utiliser le nom de service interne. L'issuer annoncé
dans le document OIDC doit rester strictement égal à
`KEYCLOAK_ISSUER`.

Variables configurables dans le fichier local `.env` :

- `KEYCLOAK_PUBLIC_URL` : URL publique de Keycloak.
- `KEYCLOAK_ISSUER` : valeur exacte attendue dans le claim `iss`.
- `KEYCLOAK_DISCOVERY_BASE_URI` : URL interne utilisée par Symfony pour
  charger la découverte OIDC et le JWKS.
- `KEYCLOAK_AUDIENCE` : audience obligatoire, par défaut `enterprise-api`.

Ne pas modifier l'issuer pour le remplacer par le nom Docker : les jetons
déjà émis et les clients locaux utilisent l'URL publique.

## Validation du jeton par Symfony

Le firewall API est stateless et utilise l'authenticator Access Token de
Symfony avec son `OidcTokenHandler`. Le handler vérifie la signature avec les
clés publiées par Keycloak, l'algorithme autorisé (`RS256`), l'issuer, l'audience
et les contraintes temporelles du jeton. Le cache Symfony conserve les
informations de découverte et les clés selon leur politique de cache.

Les routes `/health` et `/health/ready` restent publiques pour les contrôles
de santé Docker et Kong. Les routes Customer API sont protégées :

| Route | Permission |
|---|---|
| `GET /api/v1/customers` | `api.read` |
| `GET /api/v1/customers/{id}` | `api.read` |
| `POST /api/v1/customers` | `api.write` |
| `PATCH /api/v1/customers/{id}` | `api.write` |

Le fournisseur Symfony traduit les rôles Keycloak en rôles applicatifs :
`api.read` → `ROLE_API_READ`, `api.write` → `ROLE_API_WRITE`.
Le rôle `platform-admin` reçoit les deux permissions. Les autres rôles du realm,
comme `portal-user`, ne donnent aucun accès à la Customer API.

- `401 Unauthorized` : jeton absent, invalide, expiré ou non destiné à cette API.
- `403 Forbidden` : jeton valide, mais permission requise absente.

L'API n'accepte que le jeton Bearer transmis dans l'en-tête
`Authorization`. Ne jamais envoyer de jeton dans l'URL ou le journaliser.

## Démarrage et provisionnement

Créer un fichier d'environnement local privé et remplacer toutes les valeurs fictives :

~~~bash
cp .env.example .env
~~~

Générer des valeurs aléatoires pour les mots de passe et secrets locaux :

~~~bash
openssl rand -hex 32
~~~

Vérifier et démarrer les services :

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
curl --fail http://localhost:8081/health/ready
~~~

Appliquer le secret du client machine et attribuer le rôle `api.read` :

~~~bash
bash scripts/keycloak-provision.sh
~~~

## Obtenir un jeton machine et tester l'API

Le script suivant récupère un jeton Client Credentials et affiche des claims
sans imprimer le jeton ni le secret :

~~~bash
bash scripts/keycloak-token-smoke-test.sh
~~~

Pour obtenir un jeton dans le shell courant :

~~~bash
TOKEN_RESPONSE=$(curl --fail --silent --show-error --request POST \
  http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=client_credentials' \
  --data-urlencode 'client_id=enterprise-api-machine' \
  --data-urlencode "client_secret=$KEYCLOAK_MACHINE_CLIENT_SECRET")

ACCESS_TOKEN=$(printf '%s' "$TOKEN_RESPONSE" | jq -r '.access_token')
~~~

Tester d'abord les routes publiques et la protection sans jeton :

~~~bash
curl -i http://localhost:8001/health
curl -i http://localhost:8001/api/v1/customers
~~~

La première requête doit répondre `200`, la seconde `401`. Puis tester le jeton :

~~~bash
curl -i http://localhost:8001/api/v1/customers \
  --header "Authorization: Bearer $ACCESS_TOKEN"
~~~

Le client machine reçoit `api.read` : la lecture doit être autorisée, tandis
qu'une création doit répondre `403`. Pour tester `api.write`, utiliser un
utilisateur ou client auquel ce rôle a été attribué explicitement.

## Authorization Code avec PKCE (client public navigateur)

Le client `enterprise-portal` est public et ne possède aucun secret client.
Le Standard Flow est activé, tandis que Direct Access Grants et Implicit Flow
sont désactivés. PKCE avec la méthode S256 est obligatoire. Le callback local
autorisé est `http://localhost:5173/auth/callback`.

Le verifier reste dans le stockage de session temporaire du client navigateur ;
seul le challenge S256 est envoyé dans la requête d'autorisation. Ne jamais
placer un secret client dans une variable d'environnement React ni dans les
fichiers compilés.

## Rotation des clés et exploitation

Keycloak publie les clés publiques de signature actives via JWKS. Symfony
utilise la découverte OIDC et son cache pour suivre les clés publiées ; ne pas
épingler une seule clé publique indéfiniment.

Pour une rotation contrôlée :

1. Ajouter une nouvelle clé de signature dans **Realm settings → Keys**.
2. Conserver l'ancienne clé pendant la période de chevauchement.
3. Vérifier qu'un nouveau jeton est signé avec le nouveau `kid`.
4. Attendre la durée de vie maximale des jetons, augmentée de la tolérance d'horloge.
5. Retirer l'ancienne clé uniquement lorsque les jetons signés avec celle-ci
   ne peuvent plus être valides.

Surveiller la disponibilité de l'issuer/JWKS et ne jamais journaliser les
jetons d'accès, refresh tokens, codes d'autorisation ou secrets client.

## Réinitialisation locale et dépannage

L'import du realm a lieu lors de sa première création. Pour réimporter
volontairement une nouvelle définition, sauvegarder d'abord les données puis
supprimer uniquement le volume Keycloak :

~~~bash
docker compose down
docker volume rm enterprise-api-platform_keycloak_data
docker compose up -d keycloak
~~~

Cette opération supprime les utilisateurs, sessions et données locales
Keycloak, mais ne supprime pas le volume PostgreSQL.

- Journaux : `docker compose logs -f keycloak`.
- Santé : `curl -i http://localhost:8081/health/ready`.
- Après modification du secret machine dans `.env`, relancer
  `bash scripts/keycloak-provision.sh`.
- En cas d'erreur de découverte OIDC, vérifier que l'issuer publié par
  `/.well-known/openid-configuration` correspond exactement à
  `KEYCLOAK_ISSUER`, et que le conteneur API peut joindre le JWKS via
  `keycloak:8080`.
