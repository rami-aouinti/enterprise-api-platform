# Décisions d'architecture (ADR)

Les Architecture Decision Records (ADR) consignent les décisions ayant un impact important sur l'architecture, la sécurité, l'exploitation et le workflow de développement. Chaque fiche présente le contexte, la décision, les alternatives, les conséquences ainsi que les implications de sécurité et d'exploitation.

## Politique de statut

- **Proposé :** orientation choisie et documentée, en attente de validation pendant l'implémentation ou d'une revue explicite.
- **Accepté :** décision examinée et approuvée pour l'implémentation.
- **Remplacé :** décision remplacée par un ADR ultérieur ; conserver la fiche pour l'historique.
- **Rejeté :** option étudiée mais non retenue.

Ne pas réécrire silencieusement une décision acceptée. Créer un nouvel ADR et faire un lien vers la fiche remplacée lorsque l'architecture évolue.

## Index des décisions

| ADR | Sujet | Statut |
|---|---|---|
| [ADR-0001](0001-service-boundaries-and-data-ownership.md) | Frontières des services et propriété des données | Accepté |
| [ADR-0002](0002-kong-api-gateway.md) | Kong comme API Gateway | Proposé |
| [ADR-0003](0003-keycloak-oauth2-oidc-identity.md) | Keycloak et identité OAuth2/OIDC | Accepté |
| [ADR-0004](0004-rabbitmq-kafka-responsibilities.md) | Responsabilités de RabbitMQ et Kafka | Proposé |
| [ADR-0005](0005-api-versioning-and-compatibility.md) | Versionnement et compatibilité des API | Proposé |
| [ADR-0006](0006-ipaas-connector-and-workflow-model.md) | Modèle de connecteurs et workflows iPaaS | Proposé |
| [ADR-0007](0007-opentelemetry-observability.md) | Observabilité OpenTelemetry | Proposé |

## Processus de revue

1. Examiner le contexte, les alternatives et les conséquences avec le ticket d'implémentation associé.
2. Valider les hypothèses par rapport à l'édition réelle de l'outil retenu et aux contraintes d'implémentation.
3. Passer le statut à Accepté uniquement après revue.
4. Lier les tickets d'implémentation, pull requests et ADR de suivi.
5. Revoir les décisions lorsque des éléments mesurables ou de nouvelles exigences le justifient.

## Modèle

Tout nouvel ADR doit contenir : titre et numéro, statut, date, ticket associé, contexte, décision, alternatives étudiées, conséquences, implications de sécurité et d'exploitation, critères de révision.
