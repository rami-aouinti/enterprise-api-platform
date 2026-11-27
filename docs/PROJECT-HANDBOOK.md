# Guide du projet

## 1. Principes de travail

Ce dépôt est un portfolio d'ingénierie et un environnement d'apprentissage pratique. Privilégier les compromis explicités, une livraison progressive, la reproductibilité, la sécurité par défaut et des preuves apportées par les tests et la documentation.

### Définition de « prêt à démarrer »

Une fonctionnalité peut commencer lorsque :
- Son problème et le résultat attendu pour l'utilisateur ou l'entreprise sont clairs.
- Le périmètre et les éléments hors périmètre sont explicites.
- Les dépendances et les principales questions de conception sont identifiées.
- Les critères d'acceptation sont vérifiables.
- Les implications en matière de sécurité, de données et d'exploitation ont été examinées.

### Définition de « terminé »

Une fonctionnalité est terminée lorsque :
- Les critères d'acceptation sont satisfaits.
- Les tests automatisés couvrent les scénarios normaux, invalides et en erreur, selon le besoin.
- L'analyse statique, le formatage et les contrôles qualité pertinents réussissent.
- Les contrats API et les exemples sont mis à jour.
- Les implications en matière de sécurité et d'observabilité sont traitées.
- La documentation et les ADR sont mis à jour lorsque les décisions évoluent.
- L'installation Docker/locale reste reproductible.
- Le changement est commité directement sur main avec un commit ciblé et traçable.
- Les résultats réels de validation et les limites connues sont documentés.

## 2. Workflow Git

- Travailler directement sur `main` : c'est le workflow convenu pour ce dépôt de portfolio.
- Ne créer ni branche ni pull request.
- Garder chaque ticket dans des commits ciblés et logiquement regroupés.
- Utiliser Conventional Commits et inclure le numéro du ticket, par exemple : `feat(infra): add Docker Compose runtime (#14)`.
- Ne jamais commiter de secrets, clés privées, données de production ou informations personnelles réelles.
- Garder `main` dans un état fonctionnel ; valider les changements avant de les commiter lorsque l'environnement le permet.

## 3. Cycle de vie des tickets et fonctionnalités

Chaque phase est représentée par un ticket GitHub de type fonctionnalité/epic. Les tâches d'implémentation sont des tickets distincts et de taille réduite, qui précisent :
- Le contexte et le résultat attendu.
- Le périmètre et les éléments hors périmètre.
- Les critères d'acceptation.
- Les notes techniques.
- La stratégie de test.
- Les exigences de documentation.
- Les dépendances.

Utiliser les labels de manière cohérente : type:feature, type:task, type:bug, area:backend, area:frontend, area:infra, area:security, area:docs, priority:high, priority:medium, priority:low. Si les labels ne sont pas disponibles dans le dépôt, indiquer les noms souhaités dans les descriptions des tickets jusqu'à leur configuration. Ne pas supposer que des labels, jalons ou tableaux de projet existent sans les voir dans GitHub.

## 4. Attentes pour les changements directs sur main

Chaque changement doit :
- Référencer son ticket GitHub dans le message de commit ou les notes d'implémentation.
- Expliquer ce qui a changé et pourquoi.
- Inclure les commandes de test et leurs résultats réels.
- Décrire les changements de contrat API, de configuration et d'exploitation.
- Préciser les considérations de migration et de retour arrière.
- Confirmer qu'aucun secret ou donnée sensible n'est inclus.

## 5. Contrôles qualité

Backend :
- Tests PHPUnit.
- Formatage PHP-CS-Fixer ou équivalent.
- PHPStan.
- Tests de validation et de sécurité Symfony.
- Validation des contrats OpenAPI.

Frontend :
- ESLint.
- Vérification des types TypeScript.
- Tests unitaires/de composants.
- Build de production.

Plateforme :
- Validation de la configuration Docker Compose.
- Contrôles de santé des conteneurs.
- Tests d'intégration pour la gateway, l'authentification et la messagerie.
- Analyse des dépendances et des secrets dans la CI.

Les commandes exactes deviennent la référence lorsque l'outil ou l'environnement correspondant est implémenté. Ne jamais annoncer qu'un contrôle qualité a réussi sans l'avoir réellement exécuté.

## 6. Règles de sécurité

- Stocker les secrets locaux dans des fichiers `.env` ou `.env.local` ignorés par Git, ou dans un gestionnaire de secrets ; ne commiter que des valeurs fictives sûres dans les fichiers d'exemple.
- Appliquer le principe du moindre privilège aux comptes de service et utilisateurs de base de données.
- Valider l'issuer, l'audience, la signature, l'expiration et les algorithmes autorisés des JWT.
- Ne jamais journaliser de jetons Bearer, mots de passe, clés API ou champs de données sensibles.
- Appliquer la validation des entrées, les limites de taille des payloads, les timeouts et les limites de débit.
- Considérer les payloads de webhooks externes et de connecteurs comme non fiables.
- Définir les règles de conservation et de masquage des journaux d'exécution des intégrations.
- Utiliser uniquement des données synthétiques ; ne jamais commiter d'exports de production ou de vraies données clients.

Voir [Installation locale et gestion des secrets](development/local-setup.md).

## 7. Exigences de documentation

Chaque capacité architecturale doit documenter :
- Son objectif et les frontières de responsabilité.
- Le flux des requêtes/événements.
- Sa configuration.
- Les modes de panne et les procédures de récupération.
- Les considérations de sécurité.
- Les signaux d'observabilité.
- Les étapes de vérification locale.
- Les décisions prises et les alternatives.

## 8. Apprentissage et préparation aux entretiens

Pour chaque fonctionnalité majeure, conserver une explication concise de :
1. Le problème.
2. La conception choisie.
3. Les alternatives envisagées.
4. Les compromis et les modes de panne.
5. La manière dont l'implémentation est testée et exploitée.

Ne pas présenter ce projet de portfolio comme une expérience de production. Le présenter comme un projet pratique d'architecture et d'implémentation.

## 9. Prérequis locaux

Docker Engine/Desktop avec Compose v2 et Git constituent les prérequis de base. PHP 8.4/Composer 2 et Node.js/npm sont des outils facultatifs sur la machine hôte si les conteneurs ne sont pas utilisés.

Voir [Installation locale](development/local-setup.md).
