# ADR-0002 : Kong comme API Gateway

- **Statut :** Proposé
- **Date :** 2026-11-27
- **Ticket associé :** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Contexte

Les API publiées ont besoin d'un point d'entrée cohérent pour le routage, la terminaison TLS au niveau du déploiement, les limites de requêtes et les contrôles de sécurité en périphérie. Un accès public direct à chaque service dupliquerait les règles et augmenterait la surface d'attaque exposée.

## Décision

Utiliser Kong Gateway comme point d'entrée nord-sud des API. Garder la configuration de la gateway déclarative et versionnée dans Git. Kong gère le routage, les limites de requêtes en périphérie, l'intégration d'authentification configurée et la télémétrie de la gateway.

Les services métier restent responsables de la validation métier, de l'autorisation au niveau des ressources et des contrôles de propriété. Le passage par la gateway ne rend pas automatiquement une requête digne de confiance pour un service.

La configuration locale initiale utilise l'édition open source et uniquement les capacités dont la disponibilité dans cette édition a été vérifiée. Toute intégration OIDC nécessitant un plugin payant ou un composant externe doit être évaluée avant adoption.

## Alternatives étudiées

1. Pas de gateway : plus simple, mais les politiques de périphérie deviennent incohérentes et l'exposition des services est plus difficile à maîtriser.
2. Gateway Symfony personnalisée : flexible, mais reproduit des fonctionnalités déjà matures et crée une maintenance évitable.
3. Traefik ou NGINX : options de routage viables, mais le modèle de plugins orientés API et la configuration déclarative de Kong correspondent mieux aux objectifs d'apprentissage.

## Conséquences

**Avantages :** routage et politiques de périphérie centralisés ; séparation claire entre responsabilités de la gateway et du domaine ; configuration comme code, vérifiable.

**Inconvénients :** un composant supplémentaire à exploiter ; disponibilité des plugins variable selon l'édition ; risque de divergence entre politiques gateway et services sans tests communs.

## Sécurité et exploitation

Appliquer des limites prudentes aux corps et en-têtes, des timeouts et des limites de débit. Ne jamais journaliser les en-têtes Authorization ni les payloads sensibles. Valider les en-têtes transférés et les paramètres des proxies de confiance. En environnement déployé, empêcher l'accès aux endpoints des services depuis des réseaux non fiables. Maintenir les contrôles de santé et les métriques de la gateway.

## Critères de révision

Revoir cette décision si les capacités OIDC nécessaires ne sont pas disponibles dans l'édition choisie, si la configuration de la gateway devient un goulot d'étranglement ou si un proxy plus simple satisfait les exigences avec moins de complexité.
