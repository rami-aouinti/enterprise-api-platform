# Guide de contribution

Merci de contribuer à Enterprise API Integration Platform. Ce dépôt est un portfolio d'ingénierie pratique : les changements doivent être reproductibles, testés, sécurisés et documentés.

## Avant de commencer

1. Lire le [Guide du projet](docs/PROJECT-HANDBOOK.md).
2. Consulter la [Feuille de route](docs/roadmap/README.md) et le ticket GitHub concerné.
3. Vérifier que le ticket définit clairement le résultat attendu, le périmètre, les dépendances et les critères d'acceptation vérifiables.
4. Demander des précisions dans le ticket plutôt que d'élargir silencieusement le périmètre.

## Workflow sur la branche main

Ce projet utilise volontairement un workflow de travail direct sur `main` :

- Travailler directement sur `main`.
- Ne créer ni branche ni pull request.
- Traiter un seul ticket à la fois et garder les commits ciblés.
- Utiliser Conventional Commits et inclure le numéro du ticket, par exemple `feat(api): add customer endpoint (#16)`.
- Examiner l'état le plus récent de `main` avant toute modification et éviter d'écraser des changements sans rapport.

## Commits

Utiliser Conventional Commits :

- `feat(api): add customer endpoint (#16)`
- `fix(auth): reject tokens with wrong audience (#18)`
- `docs(architecture): clarify service ownership (#17)`
- `test(integration): cover retry behavior (#23)`
- `chore(ci): pin workflow action versions (#35)`

Garder les commits de taille raisonnable et expliquer la raison du changement, pas uniquement la liste des fichiers modifiés.

## Définition de « terminé »

Un changement est terminé lorsque :
- Les critères d'acceptation du ticket sont satisfaits, ou les éléments reportés ont été explicitement convenus.
- Les tests couvrent les scénarios pertinents : succès, validation, autorisation et erreurs.
- Le formatage, l'analyse statique et les contrôles qualité applicables réussissent.
- Les contrats API et les exemples correspondent à l'implémentation.
- Les implications en matière de sécurité, de confidentialité et d'observabilité sont traitées.
- La documentation et les ADR sont mis à jour.
- L'installation locale reste reproductible.
- Le changement est commité sur `main` et les résultats réels de validation sont consignés.

## Sécurité

Ne jamais commiter de véritables identifiants, clés privées, jetons d'accès, données clients ou exports de production. Utiliser des fichiers d'environnement locaux ignorés par Git et des valeurs fictives dans les fichiers d'exemple. Signaler les vulnérabilités présumées en privé au propriétaire du dépôt plutôt que de publier des détails d'exploitation dans un ticket.
