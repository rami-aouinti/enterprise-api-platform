# Workflow de développement

## Cycle de vie des tickets

1. Choisir le travail dans la première phase de la feuille de route qui n'est pas terminée.
2. Confirmer le périmètre, les éléments hors périmètre, les dépendances et les critères d'acceptation.
3. Pour une nouvelle capacité, relier la tâche au ticket de fonctionnalité correspondant à la phase.
4. Garder les changements d'implémentation ciblés ; créer un ticket de suivi pour les travaux sans rapport.
5. Mettre à jour le ticket si les hypothèses ou le périmètre changent.

## Workflow sur main

Ce dépôt est volontairement maintenu directement sur `main` :

- Ne pas créer de branches de fonctionnalité.
- Ne pas ouvrir de pull requests.
- Travailler sur un seul ticket à la fois et garder chaque changement ciblé.
- Avant toute modification, examiner l'état actuel de `main`.
- Commiter des changements cohérents avec un message Conventional Commit et le numéro du ticket.
- Valider le changement et documenter les commandes exactes ainsi que leurs résultats.
- Ne jamais commiter de secrets, d'état local généré ou de données de production.

Exemples :

- `feat(customer-api): add customer read endpoint (#16)`
- `fix(gateway): reject oversized request bodies (#19)`
- `docs(api-governance): document compatibility policy (#21)`
- `test(consumer): verify duplicate event handling (#23)`
- `chore(ci): pin workflow action versions (#35)`

## Définition de « prêt à démarrer »

- Le problème métier/technique est compris.
- Le périmètre et les éléments hors périmètre sont écrits.
- Les dépendances et risques sont connus.
- Les critères d'acceptation peuvent être vérifiés.
- La sécurité, la propriété des données et l'exploitation ont été prises en compte.

## Définition de « terminé »

- Les critères d'acceptation sont satisfaits.
- Les tests unitaires, fonctionnels, d'intégration et de contrat pertinents réussissent.
- Le formatage et l'analyse statique réussissent.
- La documentation et les exemples sont mis à jour.
- Les ADR sont créés/mis à jour pour les décisions architecturales importantes.
- Aucun identifiant ni donnée sensible n'est présent.
- Les résultats réels de validation et les limites connues sont consignés.
- Le commit est présent sur `main`.

## Labels des tickets

Le vocabulaire de labels souhaité est documenté dans le Guide du projet. Si les labels ne sont pas encore configurés dans GitHub, garder explicite la relation phase/tâche dans les titres et descriptions. Ne pas supposer qu'un label, jalon ou tableau de projet existe sans le voir dans le dépôt.
