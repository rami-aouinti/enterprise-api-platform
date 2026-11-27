# ADR-0005 : Politique de versionnement et de compatibilité des API

- **Statut :** Proposé
- **Date :** 2026-11-27
- **Ticket associé :** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Contexte

Les API sont consommées par des clients et services déployés indépendamment. Des changements de contrat non maîtrisés peuvent casser les consommateurs et compliquer l'évolution des intégrations.

## Décision

Publier les contrats REST avec OpenAPI 3.1 et conserver le contrat sous contrôle de version aux côtés de l'implémentation.

Privilégier les changements additifs et rétrocompatibles au sein d'une version majeure. Utiliser une version majeure explicite dans le chemin URL, par exemple `/api/v1`, lorsqu'un changement de contrat incompatible est inévitable. Considérer la suppression ou le renommage de champs, les changements de type, le durcissement de la validation et les changements de sémantique des réponses comme potentiellement incompatibles.

Pour chaque version incompatible, documenter les consignes de migration, une période de dépréciation et une date de retrait. Exécuter le linting OpenAPI et les contrôles de compatibilité dans la CI. Le simple fait qu'un document puisse être analysé ne prouve pas sa compatibilité.

## Alternatives étudiées

1. Aucun versionnement explicite : URLs plus propres, mais coordination des changements incompatibles difficile.
2. Versionnement par en-tête/type de média : flexible, mais moins visible et plus complexe pour cette plateforme d'apprentissage.
3. Versionner chaque changement mineur : rejeté, car cela crée inutilement plusieurs contrats parallèles.

## Conséquences

**Avantages :** les consommateurs identifient les versions de contrat ; l'évolution additive est encouragée ; les changements incompatibles sont visibles et vérifiables.

**Inconvénients :** plusieurs versions majeures peuvent devoir être supportées temporairement ; la dépréciation et les contrôles de compatibilité ajoutent du travail de processus et de CI.

## Sécurité et exploitation

Valider les schémas de requête et de réponse lorsque c'est pertinent. Ne pas divulguer les détails internes des erreurs. Les endpoints dépréciés restent soumis aux politiques d'authentification, d'autorisation, de limitation de débit et de télémétrie.

## Critères de révision

Revoir la stratégie si des interfaces non REST ou des contraintes de partenaires externes imposent une autre approche des contrats.
