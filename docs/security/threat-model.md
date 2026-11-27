# Modèle de menaces de sécurité

## Périmètre

Le modèle de menaces initial couvre les consommateurs externes, l'API Gateway, Keycloak, les services Symfony, les brokers de messages, les identifiants des connecteurs, les payloads d'intégration et la télémétrie opérationnelle.

## Actifs à protéger

- Identités humaines et machines.
- Jetons d'accès et de renouvellement OAuth2.
- Clés API et identifiants des connecteurs.
- Données d'exemple clients/contrats/sinistres.
- Contrats API et configuration.
- Payloads de messages et historique des exécutions d'intégration.
- Clés de signature, identifiants de base de données et secrets d'infrastructure.

## Frontières de confiance

1. Internet/client vers la gateway.
2. Gateway vers service interne.
3. Service vers fournisseur d'identité.
4. Service vers base de données.
5. Service vers broker et consommateurs du broker.
6. Moteur d'intégration vers connecteur tiers.
7. Portail opérateur vers API de gestion.

## Menaces et contrôles

| Menace | Exemple | Contrôles prévus |
|---|---|---|
| Authentification défaillante | JWT falsifié, expiré ou avec mauvaise audience | Validation signature, issuer, audience, expiration et algorithme ; rotation des clés |
| Autorisation défaillante | Un utilisateur accède au dossier d'un autre client | Autorisation au niveau des ressources et tests négatifs |
| Abus/épuisement des ressources | Pic de trafic ou payload volumineux | Limites de débit, quotas, limites de corps, timeouts |
| Injection | Requête ou expression de mapping malveillante | Requêtes paramétrées, parseurs sûrs, opérations de mapping autorisées |
| SSRF | Un connecteur appelle un endpoint de métadonnées interne | Liste d'autorisation des destinations, vérification DNS/IP, restrictions de sortie |
| Fuite de secrets | Un jeton apparaît dans les journaux | Masquage, gestionnaire de secrets, scan des journaux |
| Rejeu/livraison en double | Message traité plusieurs fois | Clés d'idempotence et déduplication |
| Perte de message | Commit DB réussi mais publication événementielle en échec | Transactional outbox et retry/réconciliation |
| Risque supply chain | Dépendance ou action vulnérable | Scan des dépendances, actions épinglées, politique de mise à jour |
| Télémétrie sensible | Données personnelles dans les traces/journaux | Minimisation des données, masquage, contrôles de rétention |

## Recommandations OAuth2/OIDC

- Utiliser Authorization Code avec PKCE pour la connexion des utilisateurs depuis un navigateur.
- Utiliser Client Credentials pour les accès machine-à-machine lorsque c'est adapté.
- Ne pas utiliser le grant Resource Owner Password Credentials.
- Garder les jetons d'accès de courte durée et valider l'audience et les scopes.
- Garder les secrets client hors des bundles frontend.
- Utiliser une signature asymétrique et une procédure de rotation des clés documentée lorsque c'est pris en charge.

## Plan de tests de sécurité

- Tests de jetons manquants, mal formés, expirés, avec mauvais issuer ou mauvaise audience.
- Tests de rôles/scopes insuffisants et d'accès inter-tenant/ressource.
- Tests de limitation de débit et de taille de payload.
- Tests de liste d'autorisation des URL de connecteurs et de régression SSRF.
- Tests de masquage des secrets.
- Tests de rejeu et de traitement des événements en double.
- Scan des dépendances et des images de conteneurs.

Ce modèle de menaces est évolutif. Le revoir lorsque de nouveaux connecteurs, types de données ou frontières de confiance sont introduits.
