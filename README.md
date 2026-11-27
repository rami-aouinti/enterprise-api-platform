# Enterprise API Integration Platform

A production-minded learning project that demonstrates enterprise API management, API governance, secure service integration, event-driven architecture, and an extensible iPaaS-style integration runtime.

> **Purpose:** build and explain a realistic platform relevant to a Senior API Developer role. This is an independent portfolio/learning project, not a DEVK system and not a claim that the selected vendors are mandated by DEVK.

## Goals

- Design and publish consistent REST APIs using OpenAPI.
- Route and protect APIs through a centralized API Gateway.
- Apply OAuth 2.0 / OpenID Connect, authorization, rate limits, and threat controls.
- Govern API lifecycle, compatibility, standards, and consumer access.
- Integrate heterogeneous systems through reusable connectors, mappings, and workflows.
- Use asynchronous messaging and event streaming with reliable delivery patterns.
- Operate the platform with automated tests, CI/CD, observability, and containerized infrastructure.

## Planned technology baseline

| Concern | Initial choice | Why |
|---|---|---|
| Backend services | PHP 8.4, Symfony 7 | Strong fit with the project's PHP/API focus |
| Admin portal | React, TypeScript, Vite | Typed operator and developer portal |
| API Gateway | Kong Gateway (OSS) | Routing, plugins, rate limiting, JWT/OIDC integration options |
| Identity | Keycloak | OAuth 2.0, OIDC, realms, clients, roles |
| API contract | OpenAPI 3.1 | Language-neutral API contract |
| Linting/governance | Spectral | Automated API style rules |
| Relational data | PostgreSQL | Transactional service data |
| Messaging | RabbitMQ | Work queues, retries, dead-lettering |
| Event streaming | Apache Kafka | Durable event streams and replay |
| Cache | Redis | Cache and distributed coordination where justified |
| Observability | OpenTelemetry, Prometheus, Grafana, Loki, Jaeger | Metrics, logs, traces |
| Local runtime | Docker Compose | Reproducible development environment |
| Delivery | GitHub Actions | Automated quality gates and CI |

Versions will be pinned and reviewed in implementation issues. Tool selection is a practical reference architecture, not a vendor requirement.

## Architecture at a glance

`React Developer/Admin Portal → Kong Gateway → Symfony domain APIs`

Supporting capabilities:
- Keycloak provides identity and token issuance.
- PostgreSQL stores service-owned transactional data.
- RabbitMQ handles task-oriented asynchronous messaging.
- Kafka carries domain event streams where replay/stream processing is valuable.
- The integration runtime connects source and target systems through adapters, transformations, routing, and execution history.
- OpenTelemetry instruments services; Prometheus/Grafana, Loki, and Jaeger provide operational visibility.

See [Architecture](docs/architecture/overview.md), [Technology decisions](docs/architecture/technology-decisions.md), and [Roadmap](docs/roadmap/README.md).

## Delivery approach

Work is organized into GitHub milestones (when supported) and feature issues. Each feature issue includes scope, acceptance criteria, tests, dependencies, and documentation expectations. Use small branches and pull requests; do not commit implementation directly to `main`.

1. Read [Project handbook](docs/PROJECT-HANDBOOK.md).
2. Review [Roadmap and phases](docs/roadmap/README.md).
3. Pick an open issue from the earliest unfinished phase.
4. Create a branch named `feature/<issue-number>-short-description`.
5. Implement tests and documentation with the change.
6. Open a PR referencing the issue (for example, `Closes #12`).

## Repository structure (target)

```text
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
│   ├── architecture/
│   ├── adr/
│   ├── api-governance/
│   ├── security/
│   ├── integration/
│   ├── operations/
│   └── roadmap/
└── .github/
    ├── ISSUE_TEMPLATE/
    └── PULL_REQUEST_TEMPLATE.md
```

The structure is a target, not a claim that these directories or services already exist.

## Status

**Planning and foundation documentation.** Implementation starts with Phase 0 / Phase 1 issues in GitHub. See the [open issues](https://github.com/rami-aouinti/enterprise-api-platform/issues).

## Learning outcomes

By completing the project, the developer should be able to explain API design, API lifecycle and governance, gateway responsibilities, OAuth2/OIDC flows, threat protection, synchronous vs asynchronous integration, event delivery guarantees, iPaaS connector design, observability, and operational trade-offs with concrete code and tests.
