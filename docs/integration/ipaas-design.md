# Conception du moteur d'intégration de type iPaaS

## Objectif

Fournir un petit moteur d'intégration auditable qui connecte des systèmes via des adaptateurs réutilisables et des workflows déclaratifs. L'objectif est de démontrer les concepts fondamentaux d'un iPaaS, pas de reproduire une plateforme commerciale.

## Concepts principaux

- **Définition de connecteur :** protocole pris en charge, schéma de configuration, méthode d'authentification, capacités et contrôle de santé.
- **Instance de connexion :** endpoint propre à un environnement et référence de secret. Les valeurs des secrets ne sont jamais stockées en clair dans le JSON d'un workflow.
- **Déclencheur :** webhook, interrogation planifiée (polling) ou abonnement à un broker.
- **Workflow :** ensemble ordonné d'étapes validées avec contrats d'entrée/sortie explicites.
- **Mapping :** transformation déclarative du schéma source vers le schéma de destination.
- **Exécution :** un lancement avec identifiant de corrélation, état, durées, tentatives et détails d'erreur expurgés.
- **Politique de retry :** nombre de tentatives limité, backoff exponentiel, jitter et classification des erreurs retentables.
- **État dead-letter :** échec terminal nécessitant une inspection ou un rejeu avec des permissions contrôlées.

## Première tranche fonctionnelle de bout en bout

1. Recevoir un webhook signé depuis un CRM simulé.
2. Valider le payload entrant avec un JSON Schema.
3. Normaliser l'enregistrement client source.
4. Appliquer un mapping déclaratif des champs.
5. Appeler Customer Service via la gateway.
6. Persister l'état de l'exécution et son identifiant de corrélation.
7. Retenter les erreurs transitoires ; arrêter sur les erreurs permanentes de validation/authentification.
8. Exposer l'historique des exécutions et une opération de rejeu sécurisée.

## Interface d'un connecteur

Un connecteur doit déclarer :
- Un type et une version stables.
- Un schéma de configuration.
- Les opérations prises en charge.
- Les exigences d'authentification.
- Les valeurs par défaut de timeout et de retry.
- Le comportement du contrôle de santé.
- Les références aux schémas d'entrée/sortie.

Garder le code spécifique au protocole derrière des adaptateurs. Les workflows ne doivent pas contenir de PHP arbitraire, de commandes shell ou d'expressions sans restriction.

## Fiabilité

- Utiliser des clés d'idempotence pour les écritures vers les destinations.
- Persister les transitions d'état des exécutions.
- Limiter les retries et les rendre observables.
- Utiliser un outbox lorsqu'une transaction de base doit déclencher de manière fiable la publication d'un message.
- Ne pas promettre une livraison « exactement une fois » ; concevoir pour une livraison « au moins une fois » avec traitement idempotent.

## Sécurité

- Stocker les identifiants sous forme de références de secrets.
- Autoriser explicitement les hôtes et protocoles sortants.
- Bloquer loopback, link-local, plages privées et destinations de métadonnées cloud, sauf nécessité explicite pour une fixture locale contrôlée.
- Masquer les identifiants et champs sensibles des payloads dans les journaux.
- Appliquer une autorisation par connexion et auditer les changements.

## Hors périmètre de la première version

- Exécution de code arbitraire écrit par l'utilisateur.
- Des centaines de connecteurs de production.
- Compatibilité BPMN complète.
- Haute disponibilité multi-région.
- Revendiquer une maturité SaaS prête pour la production.
