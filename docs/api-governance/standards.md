# Standards de gouvernance des API

## Développement contract-first

- Chaque API consommable de l'extérieur possède un contrat OpenAPI 3.1.
- Les changements de contrat sont examinés en même temps que les changements d'implémentation.
- Les exemples couvrent les réponses réussies et les principales réponses d'erreur.
- Les schémas définissent volontairement les champs obligatoires, formats, limites et règles de nullabilité.

## Conventions HTTP et ressources

- Utiliser des noms de ressources dans les chemins et les méthodes HTTP standard.
- Utiliser des noms de collections au pluriel (par exemple `/api/v1/customers`).
- Utiliser des codes de statut adaptés et des corps d'erreur cohérents.
- N'ajouter pagination, filtrage et tri qu'avec une sémantique documentée.
- Utiliser des horodatages UTC au format ISO 8601.
- Éviter d'exposer les identifiants internes de base de données ou les détails d'implémentation lorsque ce n'est pas approprié.

## Versionnement et compatibilité

- Versionner les chemins des API publiques sous la forme `/api/v1` dans ce projet.
- Privilégier les champs facultatifs additifs et les nouveaux endpoints pour faire évoluer le contrat de manière compatible.
- Considérer la suppression/le renommage de champs, les changements de type, l'ajout de champs obligatoires et les changements sémantiques comme potentiellement incompatibles.
- Exécuter des contrôles automatiques de différence de contrat dans la CI.
- Publier des avis de dépréciation et des consignes de migration avant le retrait d'une API.

## Modèle d'erreur

Adopter des réponses cohérentes de type Problem Details (RFC 9457), avec des types d'erreur stables et lisibles par machine. Ne jamais exposer de traces de pile, erreurs SQL, secrets ou détails de topologie interne aux consommateurs.

## Sécurité et confidentialité

- Documenter les exigences d'authentification et d'autorisation dans OpenAPI.
- Valider les entrées et limiter la taille des payloads.
- Éviter les données sensibles dans les URL.
- Définir la classification des données et les règles de masquage des journaux.
- Appliquer les scopes/rôles et l'autorisation au niveau des ressources.

## Workflow de gouvernance

1. Créer ou mettre à jour le contrat OpenAPI.
2. Exécuter les règles de linting Spectral.
3. Examiner la sécurité, la compatibilité et l'exposition des données.
4. Exécuter les tests de schéma et de contrat.
5. Approuver et publier selon le cycle de vie de l'API.
6. Surveiller l'utilisation et les erreurs.
7. Déprécier avec communication aux consommateurs et période de migration.

## Premières règles de linting à implémenter

- Titre, version et description de l'API obligatoires.
- Résumé et operationId obligatoires pour chaque opération.
- Casse cohérente des chemins et noms de ressources au pluriel.
- Définitions explicites des réponses.
- Exigences de sécurité pour les opérations protégées.
- Aucun comportement 4xx/5xx non documenté.
- Réutilisation des schémas et paramètres lorsque c'est pertinent.
- Aucun serveur pointant vers localhost dans les contrats publiés.
