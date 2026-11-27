# Repository Structure

The following is the intended structure. Directories will be created when their owning implementation tickets begin; this document does not claim that every component exists today.

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

## Ownership guidance
- apps/: user-facing portal, gateway configuration, and integration runtime applications.
- services/: independently owned business capabilities and their APIs.
- packages/: reusable technical code and published contracts only; avoid a shared domain model.
- infrastructure/: local orchestration, gateway/identity configuration, observability, and later deployment manifests.
- docs/: architecture, decisions, API governance, security, operations, development, and roadmap material.
- .github/: issue and pull request templates plus automated workflows.

Each service should own its business rules, persistence boundary, tests, and operational documentation. Cross-service communication must use published APIs or messages, not another service's private database tables.
