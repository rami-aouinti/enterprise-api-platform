# ADR-0002 : Kong comme API Gateway

- **Statut :** Accepté pour le routage et les politiques de périphérie ; validation OIDC/JWT à finaliser
- **Date :** 2026-11-27
- **Ticket associé :** [#19](https://github.com/rami-aouinti/enterprise-api-platform/issues/19)

## Décision

Utiliser Kong Gateway Community Edition 3.9.1 en mode DB-less avec une configuration déclarative versionnée dans Git. Kong centralise le routage vers Customer API, les limites de débit, la taille maximale des corps, les timeouts upstream et les identifiants de corrélation.

La validation des jetons Keycloak n'est pas activée dans cette première tranche. Le plugin JWT standard ne fournit pas à lui seul la découverte JWKS et la validation complète de l'audience Keycloak. Un plugin tiers évalué publie une matrice de compatibilité ancienne et ne couvre pas notre combinaison Kong 3.9.1 / Keycloak 26.3.3 ; il n'est donc pas retenu sans tests. Chaque API reste responsable de l'authentification, de l'autorisation métier et du contrôle d'accès aux ressources.

## Conséquences

**Avantages :** routage et politiques de périphérie centralisés ; configuration déclarative ; séparation entre responsabilités de la gateway et du domaine.

**Limites :** la validation OIDC/JWT à la gateway reste un critère ouvert ; les limites de débit locales ne sont pas distribuées entre plusieurs instances ; le mode local ne remplace pas une configuration de production avec TLS, secrets gérés et réseau privé.

## Sécurité et exploitation

- L'API d'administration Kong est liée à localhost et ne doit jamais être exposée publiquement.
- Ne jamais journaliser les en-têtes Authorization ni les payloads sensibles.
- Les services continuent à valider l'identité, les permissions et la propriété des ressources.
- Les timeouts, la taille des corps et les limites de débit doivent être adaptés à la charge mesurée.
- Ne pas exposer la gateway à un réseau non fiable avant l'activation et les tests du validateur OIDC/JWKS.

## Critère restant

Démontrer automatiquement le rejet des signatures invalides, jetons expirés, mauvais issuer et mauvaise audience avec les versions de Keycloak et Kong réellement utilisées.
