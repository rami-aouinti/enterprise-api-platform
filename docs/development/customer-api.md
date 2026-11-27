# API Customer Service

Customer Service est la première API métier de la plateforme. Elle est implémentée dans l'application Symfony et exposée sous `/api/v1`.

## Modèle de ressource

Un client contient un UUID, un prénom, un nom, une adresse e-mail unique, un numéro de téléphone facultatif, un statut de cycle de vie (`active` ou `inactive`) et des horodatages UTC de création/mise à jour. Les adresses e-mail sont nettoyées des espaces superflus et normalisées en minuscules. La base de données garantit l'unicité.

## Endpoints

| Méthode | Chemin | Objectif | Succès |
|---|---|---|---|
| GET | /api/v1/customers | Lister les clients | 200 |
| POST | /api/v1/customers | Créer un client | 201 |
| GET | /api/v1/customers/{id} | Récupérer un client | 200 |
| PATCH | /api/v1/customers/{id} | Modifier partiellement un client | 200 |

La pagination de la collection utilise `page` (à partir de 1) et `per_page` (1–100, valeur par défaut 20). La réponse contient `data` et un objet `meta` avec la page, la taille de page, le nombre total d'enregistrements et le nombre total de pages.

## Comportement en erreur

Les erreurs utilisent `application/problem+json` et la structure Problem Details de la RFC 9457. Les erreurs de validation renvoient 422 avec un objet `errors`. Un JSON mal formé renvoie 400, un type de média non pris en charge 415, un e-mail en doublon 409 et un client inexistant 404. Les détails internes de la base ne sont pas renvoyés.

## Configuration de la base de données

Appliquer les migrations SQL depuis le conteneur API avec :

    docker compose exec api php bin/console app:database:migrate

Le moteur de migration enregistre les fichiers appliqués dans `schema_migrations` et exécute chaque migration dans une transaction.

## Contrat OpenAPI

La description contract-first de l'API est maintenue dans `docs/api/customer-service.openapi.yaml` (OpenAPI 3.1). Mettre à jour le contrat et l'implémentation ensemble. Le test de contrat automatisé vérifie les opérations requises de la ressource et les schémas principaux.
