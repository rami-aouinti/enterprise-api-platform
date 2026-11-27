# Frontières des services et carte des contextes métier

**Statut :** socle de la Phase 02  
**Travaux associés :** ticket #17, ADR-0001

Ce document définit les règles de propriété et d'intégration avant l'implémentation des API Policy, Claims et Billing. Customer API est la première API métier implémentée ; les autres services ci-dessous représentent des frontières d'architecture et ne signifient pas que leur exécution est déjà disponible.

## 1. Contextes délimités et propriété

| Service | Données dont il est propriétaire | Ne doit pas posséder |
|---|---|---|
| Customer | Identité client, nom, coordonnées, cycle de vie client | Contrats, sinistres, factures, transactions de paiement |
| Policy | Numéro de contrat, produit, risque assuré, garanties, conditions tarifaires, dates d'effet, cycle de vie du contrat | Profil client, évaluation des sinistres, registre des factures/paiements |
| Claims | Référence du sinistre, détails du dommage, métadonnées/références des justificatifs, évaluation, décision, cycle de vie du sinistre | Conditions du contrat, profil client, registre des factures/paiements |
| Billing | Facture, lignes de facture, montant dû, affectation des paiements, statut de paiement, registre de facturation | Profil client, garanties du contrat, évaluation des sinistres |

Un service est l'unique propriétaire autorisé à écrire les données qui lui appartiennent. Les autres services peuvent conserver des projections de lecture limitées à leur besoin, mais ces projections ne sont pas la source de vérité et doivent pouvoir être reconstruites depuis les API/événements.

## 2. Carte des contextes

~~~mermaid
flowchart LR
    Consumer[Partenaire / Canal] --> Gateway[Kong API Gateway]
    Gateway --> Customer[Customer Service]
    Gateway --> Policy[Policy Service]
    Gateway --> Claims[Claims Service]
    Gateway --> Billing[Billing Service]

    Policy -->|API Customer : vérifier/référencer| Customer
    Claims -->|API Policy : vérifier les garanties| Policy
    Claims -->|API Customer : référence de contact si nécessaire| Customer
    Billing -->|API Customer : référence client de facture| Customer

    Policy -.->|PolicyIssued / PolicyCancelled| Events[(Événements métier)]
    Claims -.->|ClaimOpened / ClaimDecisionRecorded| Events
    Billing -.->|InvoiceIssued / PaymentReceived| Events
    Events -.-> Customer
    Events -.-> Policy
    Events -.-> Claims
    Events -.-> Billing
~~~

Les flèches pleines représentent les appels API synchrones. Les flèches pointillées représentent la publication/consommation asynchrone d'événements métier. Le broker est un transport, pas le propriétaire de l'état métier. La gateway est un point d'entrée et n'orchestre pas les règles métier.

## 3. Règles d'interaction autorisées

1. Un service peut uniquement lire ou modifier sa propre base de données.
2. Pour obtenir les faits de référence actuels d'un autre contexte, un service utilise l'API publiée par ce contexte.
3. Pour réagir à un fait métier durable d'un autre contexte, un service utilise un contrat d'événement versionné.
4. Ne jamais effectuer de jointure entre bases de services, interroger les tables d'un autre service ou écrire dans ses enregistrements.
5. Éviter les chaînes d'appels synchrones trop bavardes. Si un workflow tolère la cohérence éventuelle, préférer les événements et un état de processus local.
6. Chaque événement possède un identifiant stable, un type, une version de schéma, un horodatage d'occurrence, un producteur et un identifiant de corrélation. Les consommateurs doivent être idempotents.
7. Les événements transportent le minimum de données métier nécessaire. Préférer les identifiants d'agrégats et les faits immuables aux dossiers complets de clients, contrats ou paiements.
8. Les API synchrones ont des timeouts et des comportements d'erreur explicites. Une panne en aval ne doit pas produire silencieusement un résultat métier positif.

## 4. Premiers contrats d'intégration

| Producteur | Contrat | Consommateur | Interaction | Objectif |
|---|---|---|---|---|
| Customer | GET /api/v1/customers/{id} | Policy, Claims, Billing | REST synchrone | Valider/référencer un client ; ne retourner que les champs nécessaires à l'appelant |
| Policy | GET /api/v1/policies/{id}/coverage | Claims | REST synchrone | Vérifier les garanties pour la date du sinistre et le risque déclaré |
| Policy | policy.issued.v1 / policy.cancelled.v1 | Claims, Billing | Événement | Notifier les changements de cycle de vie sans accès à une base partagée |
| Claims | claim.opened.v1 / claim.decision-recorded.v1 | Policy, Billing | Événement | Publier les faits du cycle de vie et les décisions de sinistre |
| Billing | invoice.issued.v1 / payment.received.v1 | Consommateurs autorisés | Événement/API | Publier les faits du cycle de vie de facturation |

Il s'agit de noms de contrats logiques, pas encore d'endpoints implémentés ni de bindings de broker. Les schémas OpenAPI détaillés et les schémas d'événements AsyncAPI seront ajoutés avec les tickets du service propriétaire et de la messagerie. Aucun consommateur ne doit déduire un contrat non documenté à partir du modèle de base d'un autre service.

## 5. Exemples de workflows interservices

### Déclaration d'un sinistre
1. Claims reçoit une demande de déclaration et valide la structure de sa requête ainsi que l'autorisation.
2. Claims appelle l'endpoint de vérification des garanties de Policy avec la référence du contrat et la date du sinistre.
3. Claims stocke le sinistre et son état d'évaluation dans sa propre base.
4. Claims publie `claim.opened.v1` après validation de la transaction.
5. Billing peut consommer l'événement pour créer une tâche de facturation/revue en attente lorsque les règles métier le prévoient. Il ne modifie pas les tables Claims.

Si Policy est indisponible, Claims renvoie une erreur de dépendance pouvant être retentée ou accepte le sinistre dans un état explicite `pending_policy_verification`. Il ne doit jamais supposer que les garanties sont valides.

### Émission d'un contrat
1. Policy vérifie la référence client via Customer API.
2. Policy valide les règles de produit et de garantie dans son propre contexte.
3. Policy persiste le contrat dans sa propre base.
4. Policy publie `policy.issued.v1` après commit.
5. Billing consomme l'événement et crée sa propre facture selon ses règles.

Le workflow est à cohérence éventuelle. Il n'utilise pas de transaction distribuée entre bases.

## 6. Frontières de persistance

| Service | Base logique | Propriétaire du schéma | Identifiants |
|---|---|---|---|
| Customer | customer_service | Customer | Rôle réservé à Customer |
| Policy | policy_service | Policy | Rôle réservé à Policy |
| Claims | claims_service | Claims | Rôle réservé à Claims |
| Billing | billing_service | Billing | Rôle réservé à Billing |

Avec Docker Compose local, ces bases peuvent fonctionner sur un même serveur/conteneur PostgreSQL pour réduire l'utilisation des ressources. Elles restent des bases séparées avec des utilisateurs/identifiants distincts et des migrations indépendantes. La production peut les placer sur des instances séparées sans changer les contrats de service.

La migration actuellement implémentée pour Customer API appartient à Customer Service. Les bases Policy, Claims et Billing seront créées par leurs tickets d'implémentation respectifs ; ce document ne prétend pas qu'elles existent déjà.

## 7. Règles de panne, cohérence et sécurité

- Utiliser un outbox lorsqu'un service doit publier de manière fiable un événement correspondant à un changement de base commité.
- Les consommateurs enregistrent les identifiants des événements traités ou appliquent un autre mécanisme d'idempotence.
- Retenter les erreurs transitoires avec un backoff exponentiel limité ; diriger les messages épuisés vers une DLQ.
- Utiliser des identifiants de corrélation entre HTTP et messagerie ; ne jamais les utiliser comme justificatifs d'autorisation.
- Authentifier les appels service-à-service et autoriser l'opération demandée dans le service destinataire.
- Ne pas placer de secrets, jetons d'accès, données complètes de paiement ou données personnelles inutiles dans les événements/journaux.
- Définir les règles de rétention et de propagation des suppressions lorsque la classification concrète des données sera implémentée.

## 8. État de l'implémentation

| Capacité | Statut |
|---|---|
| API Customer et persistance PostgreSQL détenue par Customer | Implémentées dans le ticket #16 |
| Frontière, propriété et contrats Policy | Définis ; implémentation à venir |
| Frontière, propriété et contrats Claims | Définis ; implémentation à venir |
| Frontière, propriété et contrats Billing | Définis ; implémentation à venir |
| Bases/utilisateurs locaux distincts pour les quatre services | Architecture cible ; provisionnement avec les tickets d'infrastructure des services |
| Schémas d'événements versionnés et topologie broker | Définis conceptuellement ; implémentation dans les tickets de messagerie |

## 9. Liste de validation

- [x] La carte des contextes identifie la propriété et les interactions autorisées.
- [x] Chaque service possède une frontière de persistance distincte dans l'architecture cible.
- [x] Les workflows interservices utilisent des API/événements plutôt que des écritures dans une base partagée.
- [x] Les premiers contrats métier et d'intégration sont documentés.
- [ ] Les bases/utilisateurs physiques de Policy, Claims et Billing sont provisionnés avec leurs implémentations.
- [ ] Les schémas OpenAPI et événements sont ajoutés avec les implémentations API/messagerie propriétaires.
