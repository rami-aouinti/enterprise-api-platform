# Kong Gateway — configuration locale

## Démarrage

Depuis la racine du dépôt :

~~~bash
git pull --ff-only origin main
docker compose config --quiet
docker compose up -d --build kong
docker compose ps kong api
~~~

## Points d'entrée

- Proxy : `http://localhost:8001`
- API d'administration : `http://localhost:8002` (liée à localhost uniquement)
- Statut : `http://localhost:8100/status`
- API Symfony directe : `http://localhost:8000` (développement local uniquement)

## Routes et politiques

Le fichier déclaratif `infrastructure/kong/kong.yml` configure le routage de `/api/v1/customers` et des endpoints `/health` vers le service Symfony `api:8000`.

La route Customer applique 60 requêtes/minute par IP, une taille maximale de corps de 1 Mio, un identifiant `X-Correlation-ID` et des timeouts upstream de connexion/lecture/écriture de 3/10/10 secondes.

## Vérifications

~~~bash
curl -i http://localhost:8100/status
curl -i http://localhost:8001/health
curl -i http://localhost:8001/health/ready
curl -i http://localhost:8001/api/v1/customers
~~~

Pour tester la limite de taille, envoyer un corps dépassant 1 Mio sur une méthode acceptée et vérifier la réponse 413. Pour tester la limitation de débit, répéter les appels jusqu'à dépasser 60 requêtes dans la fenêtre d'une minute et vérifier la réponse 429. Les limites `policy: local` sont locales à chaque instance Kong et ne constituent pas un compteur distribué.

## Limite de sécurité importante

Cette configuration utilise Kong Community Edition et ses plugins intégrés pour le routage et les politiques de périphérie. Elle **ne valide pas encore les jetons Keycloak**. Le plugin JWT standard de Kong ne fournit pas à lui seul la validation JWKS dynamique et du claim `aud` nécessaire à notre realm. La gateway ne doit donc pas être considérée comme une frontière d'authentification. L'authentification, l'autorisation et le contrôle de propriété restent à la charge de chaque API. L'ajout d'un validateur OIDC/JWKS compatible avec Kong 3.9.1 et Keycloak 26.3.3 est un critère restant avant l'exposition sur un réseau non fiable.

L'API d'administration Kong ne doit jamais être exposée publiquement. En production, ne pas publier directement le port Symfony et ajouter TLS, gestion de secrets, réseau privé et politiques de disponibilité.
