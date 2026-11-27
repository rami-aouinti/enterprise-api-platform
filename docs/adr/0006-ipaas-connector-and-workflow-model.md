# ADR-0006 : Modèle ciblé de connecteurs et de workflows iPaaS

- **Statut :** Proposé
- **Date :** 2026-11-27
- **Ticket associé :** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Contexte

Les intégrations d'entreprise connectent des systèmes qui utilisent des protocoles, schémas de payload, modèles d'authentification et caractéristiques de fiabilité différents. Intégrer une logique spécifique à chaque connexion dans tous les services métier créerait des duplications et un couplage fort.

## Décision

Construire un moteur d'intégration pédagogique ciblé avec un registre de connecteurs source/cible typés et des workflows déclaratifs. Un workflow définit un déclencheur, des références de connecteurs, les schémas d'entrée/sortie, les étapes de mapping, l'action de destination, le timeout, la politique de retry et les métadonnées d'exécution.

Commencer avec un ensemble volontairement réduit de connecteurs : webhook/API HTTP et connecteur adossé à un broker. Les secrets des connecteurs sont des références vers des valeurs injectées à l'exécution ; ne jamais stocker de vrais identifiants dans les définitions de workflow ou les journaux d'exécution.

Valider les payloads entrants, appliquer des mappings explicites, contrôler les destinations sortantes et enregistrer le cycle de vie de chaque exécution avec identifiant de corrélation, horodatages, statut et diagnostics expurgés. Les tentatives sont limitées et utilisent un backoff ; les exécutions épuisées deviennent visibles pour un rejeu contrôlé.

Le moteur orchestre uniquement la connectivité et la transformation. Les services métier restent propriétaires des règles et invariants métier.

## Alternatives étudiées

1. Intégration point à point dans chaque service : simple pour une intégration, mais duplique les adaptateurs et produit des retries incohérents.
2. iPaaS commercial complet : réaliste dans certaines entreprises, mais hors périmètre du projet et potentiellement soumis à licence.
3. Commencer par un moteur de workflow généraliste : puissant, mais risque de construire une infrastructure d'orchestration avant d'avoir validé les besoins des connecteurs.

## Conséquences

**Avantages :** contrats de connecteurs réutilisables ; historique d'exécution cohérent ; comportement d'intégration explicite, testable et auditable ; services métier découplés des protocoles externes.

**Inconvénients :** les abstractions de connecteurs et la validation des workflows ajoutent de la complexité ; SSRF, gestion des identifiants, retries et rejeu nécessitent des contrôles ; le moteur peut devenir un goulot d'étranglement s'il absorbe la logique métier.

## Sécurité et exploitation

Se protéger contre les SSRF avec des listes d'autorisation de destinations, validation DNS/IP, blocage des plages privées/link-local/de métadonnées, contrôle des redirections et restrictions de sortie réseau. Appliquer des timeouts, limites de payload, validation de schéma, masquage des secrets, autorisation par tenant/consommateur et pistes d'audit. Le rejeu doit être autorisé et idempotent.

## Critères de révision

Revoir le modèle de workflow après l'implémentation de la première paire de connecteurs de bout en bout ; simplifier les abstractions qui ne sont pas justifiées par le comportement réel des connecteurs.
