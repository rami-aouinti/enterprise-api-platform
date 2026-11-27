# ADR-0001 : Frontières des services et propriété des données

- **Statut :** Accepté
- **Date :** 2026-11-27
- **Tickets associés :** #12, #17

## Contexte

La plateforme modélise les capacités Client, Contrat, Sinistres et Facturation. Une base de données ou un modèle métier partagé couplerait fortement les services et permettrait à un service de modifier les données d'un autre sans que leur propriétaire en soit informé.

## Décision

Utiliser les capacités métier comme frontières initiales des services :
- Customer Service est propriétaire de l'identité, du profil et des coordonnées des clients.
- Policy Service est propriétaire du cycle de vie des contrats, des risques assurés, des garanties sélectionnées et des conditions contractuelles.
- Claims Service est propriétaire de la déclaration des sinistres, des références aux justificatifs, de l'évaluation, des décisions et du cycle de vie des sinistres.
- Billing Service est propriétaire des factures, des affectations de paiements, de leur statut et des écritures de facturation.

Chaque service possède ses règles métier, son schéma, ses migrations, ses identifiants de base de données et son cycle de vie de persistance. La cible de déploiement utilise une base de données par service. En développement local, les services peuvent partager une même instance PostgreSQL, mais doivent utiliser des bases et des identifiants distincts. Le partage du serveur est un choix d'infrastructure, pas un partage de propriété des données.

Les autres services accèdent aux données détenues uniquement par des API versionnées ou des événements d'intégration publiés. Les lectures/écritures directes dans les tables d'un autre service, les entités ORM partagées et les clés étrangères interservices sont interdites. Les packages partagés sont limités aux utilitaires techniques et aux contrats versionnés, et non aux modèles métier partagés.

Les workflows interservices utilisent des API explicites ou des messages asynchrones. Les transactions distribuées ne sont pas présumées disponibles. Les workflows couvrant plusieurs services doivent modéliser explicitement leur état, leurs timeouts, leurs tentatives, leur idempotence et leur récupération.

## Alternatives étudiées

1. Monolithe modulaire et schéma unique : plus simple au départ, mais démontre moins bien la propriété indépendante des données et le déploiement autonome.
2. Microservices partageant une base/un schéma : déploiement distribué tout en conservant le couplage des données ; option rejetée.
3. Une base par service sur des instances PostgreSQL séparées : isolation d'infrastructure maximale, mais surcharge opérationnelle inutile pour l'environnement local d'apprentissage. Des bases et identifiants distincts sur un même serveur local suffisent au départ.

## Conséquences

**Avantages :** frontières claires de propriété et d'autorisation ; évolution indépendante des services ; contrats d'intégration explicites ; migrations de schéma et limites de sauvegarde/restauration indépendantes.

**Inconvénients :** les lectures interservices nécessitent des API/événements ; la cohérence éventuelle et les livraisons en double doivent être gérées ; davantage de frontières opérationnelles et de test sont nécessaires.

## Sécurité et exploitation

Appliquer l'autorisation au niveau du service pour chaque opération protégée. Chaque service ne reçoit des identifiants que pour sa propre base. Ne jamais exposer les identifiants d'une base à un autre service. Propager le contexte de trace et de corrélation à travers HTTP et la messagerie. Réduire les données personnelles et de paiement dans les événements ; publier des identifiants et des faits métier plutôt que des enregistrements complets.

## Critères de révision

Revoir les frontières si une capacité ne possède pas de cycle de vie métier indépendant, si les appels synchrones créent un couplage d'exécution excessif ou si les coûts opérationnels dépassent les bénéfices.
