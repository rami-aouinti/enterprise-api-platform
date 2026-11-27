# Feuille de route et phases de livraison

Chaque phase possède un ticket GitHub de fonctionnalité et des tickets d'implémentation. Terminer les critères d'acceptation et traiter les dépendances avant de passer à la phase suivante.

## Index des phases

| Phase | Ticket de fonctionnalité | Tickets d'implémentation |
|---|---|---|
| 00 — Fondations du projet | [#1](https://github.com/rami-aouinti/enterprise-api-platform/issues/1) | [#12 ADR](https://github.com/rami-aouinti/enterprise-api-platform/issues/12), [#13 workflow/documentation](https://github.com/rami-aouinti/enterprise-api-platform/issues/13) |
| 01 — Socle d'exécution | [#2](https://github.com/rami-aouinti/enterprise-api-platform/issues/2) | [#14 Docker Compose](https://github.com/rami-aouinti/enterprise-api-platform/issues/14), [#15 Symfony + CI](https://github.com/rami-aouinti/enterprise-api-platform/issues/15) |
| 02 — API métier | [#3](https://github.com/rami-aouinti/enterprise-api-platform/issues/3) | [#16 Customer API](https://github.com/rami-aouinti/enterprise-api-platform/issues/16), [#17 frontières des services](https://github.com/rami-aouinti/enterprise-api-platform/issues/17) |
| 03 — Identité et API Gateway | [#4](https://github.com/rami-aouinti/enterprise-api-platform/issues/4) | [#18 Keycloak](https://github.com/rami-aouinti/enterprise-api-platform/issues/18), [#19 Kong](https://github.com/rami-aouinti/enterprise-api-platform/issues/19) |
| 04 — Catalogue et gouvernance API | [#5](https://github.com/rami-aouinti/enterprise-api-platform/issues/5) | [#20 registre API](https://github.com/rami-aouinti/enterprise-api-platform/issues/20), [#21 Spectral + compatibilité](https://github.com/rami-aouinti/enterprise-api-platform/issues/21) |
| 05 — Messagerie et événements | [#6](https://github.com/rami-aouinti/enterprise-api-platform/issues/6) | [#22 RabbitMQ](https://github.com/rami-aouinti/enterprise-api-platform/issues/22), [#23 Kafka + outbox](https://github.com/rami-aouinti/enterprise-api-platform/issues/23) |
| 06 — Moteur d'intégration iPaaS | [#7](https://github.com/rami-aouinti/enterprise-api-platform/issues/7) | [#24 registre des connecteurs](https://github.com/rami-aouinti/enterprise-api-platform/issues/24), [#25 moteur de workflow](https://github.com/rami-aouinti/enterprise-api-platform/issues/25) |
| 07 — Portail développeur et administration | [#8](https://github.com/rami-aouinti/enterprise-api-platform/issues/8) | [#26 portail + catalogue](https://github.com/rami-aouinti/enterprise-api-platform/issues/26), [#27 administration](https://github.com/rami-aouinti/enterprise-api-platform/issues/27) |
| 08 — Renforcement de la sécurité | [#9](https://github.com/rami-aouinti/enterprise-api-platform/issues/9) | [#28 autorisation/SSRF](https://github.com/rami-aouinti/enterprise-api-platform/issues/28), [#29 secrets/audit/scan](https://github.com/rami-aouinti/enterprise-api-platform/issues/29) |
| 09 — Observabilité et performance | [#10](https://github.com/rami-aouinti/enterprise-api-platform/issues/10) | [#30 OpenTelemetry](https://github.com/rami-aouinti/enterprise-api-platform/issues/30), [#31 tableaux de bord + k6](https://github.com/rami-aouinti/enterprise-api-platform/issues/31) |
| 10 — Livraison, déploiement et préparation du portfolio | [#11](https://github.com/rami-aouinti/enterprise-api-platform/issues/11) | [#35 images CI + Kubernetes](https://github.com/rami-aouinti/enterprise-api-platform/issues/35), [#36 démonstration de bout en bout](https://github.com/rami-aouinti/enterprise-api-platform/issues/36) |

## Résultats attendus par phase

| Phase | Principaux résultats |
|---|---|
| 00 | Documentation, conventions du dépôt, décisions d'architecture, workflow des tickets |
| 01 | Docker Compose, squelette Symfony, PostgreSQL, contrôles de santé, CI |
| 02 | API Customer, Policy, Claims, Billing et contrats OpenAPI |
| 03 | Keycloak, Kong, OAuth2/OIDC, validation JWT, limitation de débit |
| 04 | Catalogue, cycle de vie, règles de style, contrôles des changements incompatibles |
| 05 | RabbitMQ, Kafka, outbox, consommateurs idempotents, retries/DLQ |
| 06 | Connecteurs, mappings, exécution de workflows, piste d'audit |
| 07 | Portail React pour API, consommateurs, intégrations et historique d'exécution |
| 08 | Modèle de menaces, contrôles anti-abus, secrets, audit, tests de sécurité |
| 09 | OpenTelemetry, métriques/journaux/traces, tableaux de bord, tests de charge |
| 10 | CI/CD, durcissement des conteneurs, déploiement Kubernetes, démonstration de bout en bout, documentation d'architecture, ADR et notes d'entretien |

## Critères de sortie d'une phase

Une phase est terminée uniquement lorsque :
- Les critères d'acceptation du ticket de fonctionnalité sont satisfaits.
- Tous les tickets enfants sont fermés ou explicitement reportés.
- Les tests et contrôles qualité réussissent.
- La documentation et les diagrammes reflètent l'implémentation réelle.
- Une démonstration ou procédure de vérification reproductible existe.
- Les limites connues et les prochaines étapes sont consignées.

## Ordre suggéré

Suivre les phases séquentiellement pour les dépendances fondamentales. Certaines tâches de documentation et de conception UI peuvent avancer en parallèle, mais ne pas construire des fonctionnalités du portail sur des contrats instables ni intégrer les politiques de gateway avant d'avoir défini le modèle d'identité.

## Discipline de périmètre

L'objectif est la profondeur et la capacité à défendre les choix, pas une longue liste de technologies. Privilégier une tranche fonctionnelle testée plutôt que des intégrations superficielles. Si un outil n'apporte aucune valeur réelle d'apprentissage ou métier, documenter la décision de ne pas l'utiliser.

## Note de gestion du projet

Les tickets de fonctionnalité représentent les epics de phase ; les tickets de tâche représentent le travail d'implémentation. L'intégration GitHub disponible n'exposait pas d'opérations permettant de créer des labels de dépôt, des jalons ou un tableau Projects v2. La relation phase/tâche est donc référencée ici et dans les descriptions des tickets ; aucun label, jalon ou tableau de projet n'est présenté comme déjà configuré.
