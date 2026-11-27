# ADR-0006 : Gouvernance OpenAPI et détection des changements incompatibles

- **Statut :** Accepté
- **Date :** 2026-11-27
- **Ticket associé :** [#21](https://github.com/rami-aouinti/enterprise-api-platform/issues/21)

## Contexte

Les contrats OpenAPI sont des interfaces publiques entre équipes et consommateurs déployés indépendamment. Une validation syntaxique seule ne garantit ni le respect des conventions de la plateforme ni la compatibilité des clients existants.

## Décision

Utiliser Spectral 6.17.1 avec un ruleset versionné pour appliquer les conventions OpenAPI du dépôt. Les règles imposent notamment des métadonnées de contact, des descriptions, des operationId uniques en lower camelCase, des tags déclarés, une sécurité OAuth2 documentée, les réponses 401/403 et des chemins cohérents.

Utiliser oasdiff 1.41.0 pour comparer les contrats modifiés avec leur version au commit de base. Toute rupture détectée fait échouer la CI par défaut. Un changement incompatible intentionnel exige une version majeure de info.version et une exception précise, approuvée et documentée ; la hausse de version seule ne désactive pas le contrôle. La suppression d'un contrat est bloquée et doit passer par une dépréciation et une migration majeure.

Les exceptions sont rares, limitées à une opération et à un changement précis. Elles doivent inclure un ticket, un propriétaire, un reviewer, une date d'expiration et une justification. Le registre est versionné et vérifié par CI.

## Alternatives étudiées

1. Uniquement Spectral : rejeté, car un linter de style ne compare pas deux versions et ne détecte pas les ruptures.
2. Uniquement oasdiff : rejeté, car la compatibilité ne remplace pas les conventions de conception.
3. Désactiver globalement les contrôles pour les exceptions : rejeté, car cela masquerait des changements non approuvés.

## Conséquences

**Avantages :** conventions reproductibles, détection précoce des ruptures, exceptions traçables et temporaires.

**Inconvénients :** les contrats doivent être maintenus avec soin ; les changements incompatibles exigent une procédure de migration et une revue explicite.
