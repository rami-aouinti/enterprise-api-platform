# ADR-0004 : Séparer les responsabilités de RabbitMQ et Kafka

- **Statut :** Proposé
- **Date :** 2026-11-27
- **Ticket associé :** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Contexte

La plateforme a besoin de traitements asynchrones et de distribution d'événements métier. Utiliser un seul broker pour tous les cas peut masquer les garanties de livraison ; exploiter deux brokers sans responsabilités distinctes ajoute une complexité injustifiée.

## Décision

Utiliser RabbitMQ pour la messagerie de tâches et de files de travail, les traitements asynchrones de type commande, les accusés de réception, les tentatives limitées et les files de lettres mortes (DLQ).

Utiliser Kafka uniquement pour les flux durables d'événements métier orientés ajout, lorsque plusieurs groupes de consommateurs indépendants, la rétention, le rejeu ou le traitement de flux apportent un bénéfice clair.

Ne pas publier chaque message dans les deux systèmes par défaut. Choisir le broker selon le besoin de communication et documenter les garanties de livraison, l'ordre, la rétention et les possibilités de rejeu.

Partir du principe que la livraison est « au moins une fois ». Les consommateurs doivent être idempotents. Pour les changements d'état en base qui publient des événements, utiliser un transactional outbox ou un mécanisme fiable équivalent. Ne pas promettre un traitement métier « exactement une fois ».

## Alternatives étudiées

1. RabbitMQ uniquement : exploitation plus simple et suffisant pour les files de travail, mais moins adapté aux flux d'événements conservés et rejouables.
2. Kafka uniquement : journal d'événements puissant, mais inutilement complexe pour de simples files de tâches.
3. Les deux brokers pour tous les messages : rejeté, car cela double les coûts opérationnels et de cohérence sans besoin défini.

## Conséquences

**Avantages :** séparation claire entre distribution de tâches et flux d'événements ; garanties de livraison explicites ; rejeu et consommateurs indépendants lorsque justifiés.

**Inconvénients :** deux technologies de broker augmentent les ressources locales ; les équipes doivent maîtriser des règles distinctes de routage, rétention, tentatives et gestion des erreurs.

## Sécurité et exploitation

Utiliser des connexions authentifiées et des utilisateurs, vhosts et topics à privilèges limités. Limiter la taille et la durée de conservation des messages. Surveiller la profondeur des files, le retard des consommateurs, les tentatives et le volume des DLQ. Éviter les données personnelles sensibles dans les événements sauf nécessité et protection adaptées.

## Critères de révision

Réévaluer la pertinence de Kafka après le premier véritable cas d'usage de flux d'événements. S'il n'existe aucun besoin de rejeu ou de consommateurs indépendants, différer Kafka plutôt que de l'exploiter uniquement pour l'apparence.
