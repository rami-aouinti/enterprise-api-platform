# Vue d'ensemble de l'architecture

## Contexte

La plateforme démontre comment une organisation peut exposer et gouverner ses API tout en intégrant des systèmes métier détenus indépendamment et des partenaires externes. Le domaine d'exemple s'inspire de l'assurance (clients, contrats, sinistres, facturation), mais l'architecture reste générique.

## Composants logiques

### Couche expérience
- **Portail Développeur/Administration (React) :** catalogue API, cycle de vie des API, applications consommatrices, configuration des intégrations, historique d'exécution et vues opérationnelles.
- **Consommateurs API externes :** applications partenaires et canaux internes. Ils n'accèdent jamais directement aux composants internes des services.

### Périphérie et identité
- **Kong Gateway :** point d'entrée unique et géré pour les API publiées ; sélection des routes, terminaison TLS lors du déploiement, limites de requêtes, intégration de l'authentification/autorisation et télémétrie de la gateway.
- **Keycloak :** fournisseur d'identité pour les utilisateurs et clients machine ; émet des jetons OAuth2/OIDC. La gateway et les services valident les jetons selon des frontières de confiance explicites.

### Services métier
- **Customer Service :** profil et coordonnées des clients.
- **Policy Service :** cycle de vie des contrats, risques assurés et garanties.
- **Claims Service :** déclaration, évaluation, décisions et cycle de vie des sinistres.
- **Billing Service :** factures, affectations de paiements et statut des paiements.

Chaque service possède ses invariants métier, sa base de données, ses migrations et ses identifiants. Les services exposent des contrats versionnés ; les workflows interservices utilisent des API ou des événements, jamais les tables privées d'un autre service. Voir [Frontières des services et carte des contextes métier](service-boundaries.md).

### Intégration et messagerie
- **RabbitMQ :** messagerie de tâches/files de travail, traitements asynchrones de type commande, retries et files de lettres mortes.
- **Kafka :** flux d'événements métier orientés ajout lorsque plusieurs consommateurs, le rejeu et les offsets indépendants sont utiles.
- **Moteur d'intégration (type iPaaS) :** registre de connecteurs, configuration source/cible, mapping, validation, routage, exécution de workflows, politique de retry et historique d'exécution.

### Données et exploitation
- **PostgreSQL :** bases transactionnelles détenues par les services et configuration de la plateforme. En local, un serveur PostgreSQL peut être partagé tout en conservant des bases et identifiants distincts pour chaque service.
- **Redis :** cache uniquement lorsqu'un besoin mesuré le justifie.
- **OpenTelemetry :** instrumentation commune pour les traces, métriques et journaux.
- **Prometheus/Grafana :** métriques et tableaux de bord.
- **Loki :** centralisation des journaux.
- **Jaeger :** exploration des traces distribuées.

## Flux principal d'une requête

1. Un consommateur obtient un jeton auprès de Keycloak avec un flux OAuth2 adapté.
2. Le consommateur appelle une API publiée via Kong.
3. Kong applique le routage et les politiques de périphérie configurées.
4. Le service Symfony propriétaire valide l'autorisation et les règles métier.
5. Le service lit ou écrit dans sa propre base de données.
6. Pour les effets de bord interservices, il émet un événement ou place un travail en file via un mécanisme de publication fiable.
7. Les consommateurs traitent les messages de manière idempotente et exposent santé, métriques et traces.

## Flux d'intégration

1. Un opérateur configure un connecteur source et un connecteur de destination.
2. Un déclencheur (polling, webhook ou message) démarre un workflow.
3. Le moteur valide et normalise le payload source.
4. Un mapping transforme les champs source vers le contrat cible.
5. Le moteur appelle l'adaptateur de destination ou publie un message.
6. Les retries suivent des règles de backoff limitées ; les messages épuisés passent dans un état de lettre morte.
7. Chaque exécution enregistre les identifiants de corrélation, le statut, la durée et des diagnostics expurgés.

## Frontières importantes

- La gateway ne remplace pas l'autorisation métier ni la validation métier.
- Keycloak authentifie les identités ; chaque API autorise toujours les actions et scopes.
- Kafka et RabbitMQ sont complémentaires et non interchangeables par défaut.
- Le moteur d'intégration orchestre la connectivité et la transformation ; les services métier restent propriétaires des invariants métier.
- L'observabilité doit propager le contexte de trace/corrélation à travers HTTP et la messagerie.
- Aucun service ne doit dépendre du schéma privé de base de données d'un autre service.

## Décisions d'architecture

Les décisions initiales sont documentées dans les ADR suivants :
- [ADR-0001 — Frontières des services et propriété des données](../adr/0001-service-boundaries-and-data-ownership.md)
- [ADR-0002 — Kong API Gateway](../adr/0002-kong-api-gateway.md)
- [ADR-0003 — Keycloak et OAuth2/OIDC](../adr/0003-keycloak-oauth2-oidc-identity.md)
- [ADR-0004 — Responsabilités de RabbitMQ et Kafka](../adr/0004-rabbitmq-kafka-responsibilities.md)
- [ADR-0005 — Versionnement et compatibilité des API](../adr/0005-api-versioning-and-compatibility.md)
- [ADR-0006 — Modèle de connecteurs et workflows iPaaS](../adr/0006-ipaas-connector-and-workflow-model.md)
- [ADR-0007 — Observabilité OpenTelemetry](../adr/0007-opentelemetry-observability.md)

## Évolution du déploiement

Commencer avec Docker Compose pour obtenir un environnement local reproductible. Introduire les manifests Kubernetes uniquement lorsque la santé des services, la configuration, les probes, les limites de ressources et le comportement opérationnel sont maîtrisés. Une démonstration locale n'est pas automatiquement prête pour la production.

## Diagrammes d'architecture

Les diagrammes évolueront avec l'implémentation. Conserver les diagrammes Mermaid dans ce répertoire et les mettre à jour lorsque les flux ou frontières de propriété changent.
