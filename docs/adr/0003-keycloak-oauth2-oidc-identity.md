# ADR-0003 : Keycloak pour OAuth 2.0 et OpenID Connect

- **Statut :** Accepté
- **Date :** 2026-11-27
- **Ticket associé :** [#18](https://github.com/rami-aouinti/enterprise-api-platform/issues/18)

## Contexte

La plateforme a besoin d'un fournisseur d'identité standard pour les utilisateurs humains, le portail développeur et les consommateurs machine-à-machine. L'authentification doit être séparée de l'autorisation propre à l'application.

## Décision

Utiliser Keycloak comme fournisseur d'identité auto-hébergé et serveur d'autorisation pour l'environnement local de référence. Utiliser OpenID Connect Authorization Code avec PKCE (S256) pour les clients publics exécutés dans le navigateur, et OAuth 2.0 Client Credentials pour les clients machine confidentiels.

Le realm local `enterprise-platform` est importé depuis une configuration versionnée. Il crée un client public `enterprise-portal`, une audience de serveur de ressources `enterprise-api`, un client machine confidentiel `enterprise-api-machine`, un utilisateur de test synthétique, des rôles de realm et un mapper d'audience API. Le secret du client machine est injecté depuis le fichier d'environnement local ignoré par Git et appliqué après l'import du realm ; il ne fait pas partie de l'export du realm.

Définir des clients distincts pour le portail et les consommateurs API. Utiliser des rôles/scopes et audiences limités au strict nécessaire. Les services valident indépendamment la signature du jeton d'accès, l'issuer, l'audience, l'expiration, la date de début de validité si présente et les algorithmes autorisés. Ils associent ensuite les claims validés aux permissions applicatives et appliquent l'autorisation au niveau des ressources.

Ne pas utiliser le grant password ni le flux implicit. Ne pas considérer les ID tokens comme des jetons d'accès API. Les secrets des clients confidentiels sont injectés à l'exécution ; ils ne sont jamais committés ni exposés dans les ressources frontend.

## Alternatives étudiées

1. Utilisateurs et JWT gérés par l'application : moins d'infrastructure, mais duplique le cycle de vie des identités et la sécurité des jetons.
2. Fournisseur d'identité externe géré : réaliste dans certaines entreprises, mais ajoute des dépendances à un compte externe et peut engendrer des coûts.
3. Autres fournisseurs d'identité auto-hébergés : options viables ; Keycloak offre un ensemble étendu de fonctions OAuth2/OIDC et un environnement local pratique.

## Conséquences

**Avantages :** identité standard pour les utilisateurs et les machines ; gestion centralisée des utilisateurs/clients et des clés de signature ; configuration locale reproductible et démonstration des flux de jetons.

**Inconvénients :** la configuration des realms/clients ajoute de la complexité ; les URL de callback et la rotation des clés nécessitent une attention opérationnelle ; la validation doit rester cohérente entre gateway et services.

## Sécurité et exploitation

Utiliser Authorization Code avec PKCE pour les clients navigateur. Garder les jetons d'accès de courte durée et configurer explicitement le comportement des refresh tokens. Valider l'issuer, l'audience, la signature, l'expiration, la date de début de validité si présente et la liste des algorithmes autorisés. Ne jamais journaliser les jetons. Prévoir la rotation des clés de signature et l'indisponibilité du fournisseur d'identité.

Le realm local utilise des jetons d'accès de cinq minutes, la rotation des refresh tokens pour les sessions interactives et un endpoint JWKS pour distribuer les clés publiques. Le realm de développement local utilise HTTP et des identifiants synthétiques uniquement ; les déploiements de production exigent TLS, une gestion sécurisée des secrets, une configuration Keycloak renforcée et des origines de redirection explicites.

## Critères de révision

Revoir le modèle de clients et de flux lorsque le portail, les partenaires externes et les cas service-à-service seront implémentés et que leurs frontières de confiance pourront être testées.
