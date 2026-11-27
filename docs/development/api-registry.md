# API Registry

Le registre centralise les métadonnées des API de la plateforme et conserve chaque contrat OpenAPI dans une version immuable.

## Modèle de données

- api_registry : nom, description, équipe propriétaire, tags et état de cycle de vie (draft, published, deprecated).
- api_registry_versions : version sémantique et document OpenAPI JSONB associé.
- Une version est unique pour une API. Un PATCH des métadonnées ne modifie pas les contrats déjà enregistrés.
- Les tables appartiennent au contexte API Registry ; aucune dépendance aux tables Customer n'est introduite.

## Endpoints

| Méthode | Endpoint | Scope | Description |
|---|---|---|---|
| GET | /api/v1/registry/apis | apis:read | Recherche et pagination |
| POST | /api/v1/registry/apis | apis:write | Enregistrement |
| GET | /api/v1/registry/apis/{id} | apis:read | Détail et versions |
| PATCH | /api/v1/registry/apis/{id} | apis:write | Métadonnées et cycle de vie |
| POST | /api/v1/registry/apis/{id}/versions | apis:write | Enregistrement d'un contrat |
| GET | /api/v1/registry/apis/{id}/versions/{version} | apis:read | Récupération du contrat |

La recherche q porte sur le nom, la description et le propriétaire. Les filtres state et tag sont combinables. La pagination utilise page et per_page (maximum 100), avec un ordre stable par nom puis UUID.

## Enregistrer une API

~~~http
POST /api/v1/registry/apis
Authorization: Bearer <token with apis:write>
Content-Type: application/json

{
  "name": "Customer API",
  "description": "Customer lifecycle endpoints",
  "owner": "Customer Platform",
  "tags": ["customers", "rest"],
  "lifecycle_state": "draft"
}
~~~

## Enregistrer un contrat

~~~http
POST /api/v1/registry/apis/{id}/versions
Authorization: Bearer <token with apis:write>
Content-Type: application/json

{
  "version": "1.0.0",
  "document": {
    "openapi": "3.1.0",
    "info": { "title": "Customer API", "version": "1.0.0" },
    "paths": {}
  }
}
~~~

Le registre accepte OpenAPI 3.0.x et 3.1.x, vérifie info.title, info.version et paths, et exige que info.version corresponde à la version enregistrée. Cette validation structurelle n'est pas un remplacement de Spectral ou d'un validateur OpenAPI complet ; la gouvernance détaillée est traitée par P04.2.

## Migration et tests

~~~bash
docker compose exec -T api php bin/console app:database:migrate
docker compose exec -T api composer test
docker compose exec -T api composer analyse
docker compose exec -T api composer cs:check
bash scripts/keycloak-scope-smoke-test.sh
~~~
