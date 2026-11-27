# Structure du dépôt

L'arborescence suivante représente la structure cible. Les répertoires seront créés lorsque les tickets d'implémentation responsables commenceront ; ce document ne prétend pas que tous les composants existent déjà.

~~~text
.
├── apps/
│   ├── api-gateway-config/
│   ├── developer-portal/
│   └── integration-runtime/
├── services/
│   ├── customer-service/
│   ├── policy-service/
│   ├── claims-service/
│   └── billing-service/
├── packages/
│   └── shared-contracts/
├── infrastructure/
│   ├── docker/
│   ├── kong/
│   ├── keycloak/
│   ├── observability/
│   └── kubernetes/
├── docs/
│   ├── adr/
│   ├── api-governance/
│   ├── architecture/
│   ├── development/
│   ├── integration/
│   ├── operations/
│   ├── roadmap/
│   └── security/
└── .github/
    ├── ISSUE_TEMPLATE/
    └── PULL_REQUEST_TEMPLATE.md
~~~

## Règles de propriété

- `apps/` : portail utilisateur, configuration de la gateway et applications du moteur d'intégration.
- `services/` : capacités métier indépendantes et leurs API.
- `packages/` : code technique réutilisable et contrats publiés uniquement ; éviter un modèle métier partagé.
- `infrastructure/` : orchestration locale, configuration gateway/identité, observabilité et futurs manifests de déploiement.
- `docs/` : architecture, décisions, gouvernance API, sécurité, exploitation, développement et feuille de route.
- `.github/` : modèles de tickets et de pull requests ainsi que workflows automatisés.

Chaque service doit posséder ses règles métier, sa frontière de persistance, ses tests et sa documentation opérationnelle. La communication interservices doit utiliser des API ou messages publiés, jamais les tables privées de base de données d'un autre service.
