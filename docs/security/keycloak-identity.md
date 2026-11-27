# Keycloak : identité et flux de jetons

## Fournisseur d'identité local

Docker Compose démarre Keycloak sur `http://localhost:8081` et importe le
realm `enterprise-platform` depuis
`infrastructure/keycloak/realm/enterprise-platform-realm.json`.

Le realm local configure :

| Composant | Objectif |
|---|---|
| `enterprise-portal` | Client public React/navigateur utilisant Authorization Code + PKCE (S256) |
| `enterprise-api` | Identifiant d'audience du serveur de ressources |
| `enterprise-api-machine` | Client machine-à-machine confidentiel utilisant Client Credentials |
| `portal-demo` | Utilisateur synthétique pour les tests interactifs |
| `portal-user`, `api.read`, `api.write`, `platform-admin` | Rôles du realm |
| `enterprise-api-audience` | Ajoute `enterprise-api` à l'audience du jeton d'accès |

Le mot de passe de l'utilisateur de démonstration local est `local-demo-only`.
Ne jamais le réutiliser en dehors de ce realm de développement jetable.

## Démarrage et provisionnement

Créer un fichier d'environnement local privé et remplacer toutes les valeurs fictives :

~~~bash
cp .env.example .env
~~~

Utiliser des valeurs aléatoires compatibles avec le shell pour les trois mots de passe/secrets. Exemple :

~~~bash
openssl rand -hex 32
~~~

Démarrer la plateforme et attendre que Keycloak soit prêt :

~~~bash
docker compose config --quiet
docker compose up -d
docker compose ps
curl --fail http://localhost:8081/health/ready
~~~

Appliquer le secret du client machine depuis le fichier `.env` ignoré par Git
et attribuer à son compte de service le rôle `api.read`, selon le principe du
moindre privilège :

~~~bash
sh scripts/keycloak-provision.sh
~~~

Le secret du client confidentiel est fourni au conteneur à l'exécution. Il
n'est stocké ni dans l'export du realm, ni dans le code source, ni dans les
ressources du navigateur. Pour le récupérer ou le faire tourner, utiliser la
configuration locale des secrets puis relancer le provisionnement.

## Authorization Code avec PKCE (client public navigateur)

Le client du portail est public : il ne possède aucun secret client. Le
Standard Flow est activé, tandis que Direct Access Grants et Implicit Flow
sont désactivés. PKCE avec la méthode de challenge S256 est obligatoire.
La liste locale des callbacks autorisés est
`http://localhost:5173/*`.

Générer un verifier et un challenge S256 :

~~~bash
CODE_VERIFIER=$(openssl rand -base64 64 | tr -d '=+/\n' | cut -c1-64)
CODE_CHALLENGE=$(printf '%s' "$CODE_VERIFIER" | openssl dgst -sha256 -binary | openssl base64 -A | tr '+/' '-_' | tr -d '=')
echo "$CODE_VERIFIER"
echo "$CODE_CHALLENGE"
~~~

Ouvrir cette URL d'autorisation dans un navigateur en remplaçant
`URL_ENCODED_CHALLENGE` par le challenge généré :

~~~text
http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/auth?client_id=enterprise-portal&response_type=code&scope=openid%20profile%20email&redirect_uri=http%3A%2F%2Flocalhost%3A5173%2Fauth%2Fcallback&code_challenge=URL_ENCODED_CHALLENGE&code_challenge_method=S256
~~~

Se connecter avec `portal-demo` et le mot de passe réservé au développement.
Le navigateur redirige ensuite vers le callback avec un code d'autorisation
de courte durée. Échanger ce code depuis l'application cliente en utilisant
le verifier initial :

~~~bash
curl --fail --request POST \
  http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=authorization_code' \
  --data-urlencode 'client_id=enterprise-portal' \
  --data-urlencode 'redirect_uri=http://localhost:5173/auth/callback' \
  --data-urlencode 'code=AUTHORIZATION_CODE_FROM_CALLBACK' \
  --data-urlencode "code_verifier=$CODE_VERIFIER"
~~~

L'URI de redirection doit correspondre exactement à celle utilisée dans la
requête d'autorisation. Le verifier reste dans le stockage de session
temporaire de l'application navigateur ; seul le challenge S256 est envoyé
dans la requête d'autorisation. Ne jamais placer un secret client dans une
variable d'environnement React ni dans les fichiers compilés.

## Client Credentials (identité machine)

Après le provisionnement, exécuter le test rapide local :

~~~bash
sh scripts/keycloak-token-smoke-test.sh
~~~

Ce script obtient un jeton d'accès pour `enterprise-api-machine` sans session
utilisateur et affiche certains claims (jamais le jeton ni le secret client).
Le compte de service reçoit `api.read` ; ne pas attribuer par défaut de rôles
administratifs étendus aux clients machine.

Requête équivalente pour obtenir le jeton :

~~~bash
curl --fail --request POST \
  http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=client_credentials' \
  --data-urlencode 'client_id=enterprise-api-machine' \
  --data-urlencode "client_secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"
~~~

## Discovery, issuer, audience et validation JWT

Document de découverte du realm :

- `http://localhost:8081/realms/enterprise-platform/.well-known/openid-configuration`

Issuer attendu :

- `http://localhost:8081/realms/enterprise-platform`

L'endpoint JWKS est publié dans le champ `jwks_uri` du document de découverte. Il est normalement :

- `http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/certs`

Chaque API/serveur de ressources doit valider un jeton d'accès avant de faire
confiance à ses claims :

1. Vérifier la signature avec une clé publique de confiance provenant du JWKS de l'issuer.
2. Autoriser uniquement l'algorithme de signature asymétrique configuré (ne pas accepter `none` ni choisir un algorithme à partir d'un en-tête de jeton non fiable).
3. Exiger une correspondance exacte de l'issuer.
4. Exiger l'audience attendue `enterprise-api` ; une signature valide ne suffit pas.
5. Vérifier `exp`, `nbf` lorsqu'il est présent et appliquer une tolérance d'horloge réduite et bornée.
6. Appliquer l'autorisation applicative avec les rôles/scopes après l'authentification.
7. Rejeter les ID tokens lorsqu'un jeton d'accès API est attendu.

La durée de vie des jetons d'accès du realm est de cinq minutes. Les refresh
tokens sont renouvelés par rotation (`revokeRefreshToken=true`,
`refreshTokenMaxReuse=0`) pour les sessions interactives. Les clients machine
doivent demander un nouveau jeton d'accès lorsque nécessaire plutôt que de
conserver des jetons de longue durée.

Ce ticket provisionne le fournisseur d'identité et démontre les deux flux
OAuth2. Il n'ajoute pas encore d'authenticator JWT Symfony ni de protection
des routes Customer API ; l'application des règles au niveau API/gateway et
l'autorisation sont traitées dans les travaux ultérieurs de sécurité/gateway.

## Rotation des clés de signature et cache JWKS

Keycloak publie les clés publiques de signature actives via JWKS. Les serveurs
de ressources doivent mettre ces clés en cache pour une durée limitée,
sélectionner la clé à partir du `kid` de l'en-tête JWT et actualiser JWKS une
fois lorsqu'un `kid` inconnu est rencontré. Ils ne doivent pas épingler une
seule clé publique indéfiniment.

Pour une rotation contrôlée :

1. Ajouter/générer une nouvelle clé de signature active dans **Realm settings → Keys**.
2. Conserver l'ancienne clé pendant la période de chevauchement afin que les jetons déjà émis restent vérifiables.
3. Confirmer que discovery/JWKS expose la nouvelle clé et vérifier un nouveau jeton avec son `kid`.
4. Attendre au moins la durée de vie maximale des jetons d'accès, augmentée de la tolérance d'horloge.
5. Retirer l'ancienne clé uniquement lorsque les jetons signés avec celle-ci ne peuvent plus être valides.

Surveiller la disponibilité de l'issuer/JWKS et ne jamais journaliser les
jetons d'accès, refresh tokens, codes d'autorisation ou secrets client.

## Réinitialisation locale et dépannage

L'import du realm a lieu lors de sa première création. Si une nouvelle
définition du realm doit volontairement être importée, sauvegarder d'abord
toute donnée importante et supprimer uniquement le volume Keycloak :

~~~bash
docker compose down
docker volume rm enterprise-api-platform_keycloak_data
docker compose up -d keycloak
~~~

Cette opération supprime les utilisateurs, sessions et données du realm
Keycloak locaux. Elle ne supprime pas le volume PostgreSQL.

- Consulter les journaux : `docker compose logs -f keycloak`.
- Vérifier la disponibilité : `curl -i http://localhost:8081/health/ready`.
- Si un secret change dans `.env`, relancer `sh scripts/keycloak-provision.sh`.
- Si le navigateur reçoit `invalid_redirect_uri`, comparer le callback avec l'URI exacte enregistrée sur `enterprise-portal`.
