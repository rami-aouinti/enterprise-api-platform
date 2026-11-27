# Enterprise API Integration Platform

A production-minded learning project that demonstrates enterprise API management, API governance, secure service integration, event-driven architecture, and an extensible iPaaS-style integration runtime.

> **Purpose:** build and explain a realistic platform relevant to a Senior API Developer role. This is an independent portfolio/learning project, not a DEVK system and not a claim that the selected vendors are mandated by DEVK.

## Current implementation

The initial local runtime is available through Docker Compose:

- PHP 8.4 API container with basic liveness and PostgreSQL readiness endpoints.
- PostgreSQL 17 with a persistent named volume.
- A dedicated Docker bridge network.
- Container health checks and startup dependency ordering.
- Local environment variables loaded from an ignored `.env` file.

The PHP endpoint is deliberately a small runtime bootstrap. The next backend ticket introduces the Symfony application and replaces this bootstrap without changing the local developer workflow.

## Start locally

Requirements: Git, Docker Engine/Desktop, and Docker Compose v2.

~~~bash
cp .env.example .env
# Edit .env and set a private local POSTGRES_PASSWORD.
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

Verify both API endpoints:

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
~~~

Expected responses are HTTP 200. The readiness endpoint confirms that the API can establish a PostgreSQL connection.

Useful commands:

~~~bash
# Follow all service logs
docker compose logs -f

# Follow one service
docker compose logs -f api
docker compose logs -f database

# Stop containers but preserve the database volume
docker compose down

# Start again
docker compose up -d

# Destructively reset the database volume and all local database data
docker compose down -v
docker compose up -d --build
~~~

The API is exposed on 127.0.0.1:8000 and PostgreSQL on 127.0.0.1:5432. The database is also reachable by the API container at database:5432. Do not use real or production data.

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

React Developer/Admin Portal → Kong Gateway → Symfony domain APIs

Supporting capabilities:
- Keycloak provides identity and token issuance.
- PostgreSQL stores service-owned transactional data.
- RabbitMQ handles task-oriented asynchronous messaging.
- Kafka carries domain event streams where replay/stream processing is valuable.
- The integration runtime connects source and target systems through adapters, transformations, routing, and execution history.
- OpenTelemetry instruments services; Prometheus/Grafana, Loki, and Jaeger provide operational visibility.

See [Architecture](docs/architecture/overview.md), [Technology decisions](docs/architecture/technology-decisions.md), [ADRs](docs/adr/README.md), and [Roadmap](docs/roadmap/README.md).

## Documentation

- [Project Handbook](docs/PROJECT-HANDBOOK.md) — engineering principles, Definition of Done, quality and security rules.
- [Contributing Guide](CONTRIBUTING.md) — commit conventions and the main-branch workflow.
- [Development Workflow](docs/development/workflow.md) — issue lifecycle and delivery conventions.
- [Local Setup](docs/development/local-setup.md) — prerequisites, Docker commands, and secret handling.
- [Repository Structure](docs/development/repository-structure.md) — intended code and documentation layout.
- [Architecture Overview](docs/architecture/overview.md) — components, boundaries, and flows.
- [Technology Decisions](docs/architecture/technology-decisions.md) — initial technology baseline and trade-offs.
- [Architecture Decision Records](docs/adr/README.md) — numbered and reviewable architecture decisions.
- [API Governance](docs/api-governance/standards.md) — contract and lifecycle standards.
- [Security Threat Model](docs/security/threat-model.md) — initial threat areas and mitigations.
- [iPaaS Design](docs/integration/ipaas-design.md) — connector and workflow design.
- [Observability](docs/operations/observability.md) — metrics, logs, and traces.
- [Roadmap](docs/roadmap/README.md) — phases, feature issues, and implementation tickets.

## Delivery approach

Work is organized into phase-level feature issues and smaller implementation issues. We work directly on `main` for this repository, with focused commits per ticket. Do not create branches or pull requests for this project. Every change must reference its issue in the commit message or implementation notes, include relevant tests, and update documentation.

## Repository structure (target)

~~~text
.
├── services/
│   ├── customer-service/
│   ├── policy-service/
│   ├── claims-service/
│   └── billing-service/
├── apps/
│   ├── api-gateway-config/
│   ├── developer-portal/
│   └── integration-runtime/
├── packages/
│   └── shared-contracts/
├── infrastructure/
│   ├── docker/
│   ├── kong/
│   ├── keycloak/
│   ├── observability/
│   └── kubernetes/
└── docs/
~~~

The structure is a target, not a claim that all listed services already exist.

## Status

**Phase 01 — Runtime foundation in progress.** Docker Compose and the initial API/database containers are committed. Symfony application setup, CI, domain APIs, gateway, identity, messaging, integration runtime, portal, and observability follow in the roadmap.

## Learning outcomes

By completing the project, the developer should be able to explain API design, API lifecycle and governance, gateway responsibilities, OAuth2/OIDC flows, threat protection, synchronous vs asynchronous integration, event delivery guarantees, iPaaS connector design, observability, and operational trade-offs with concrete code and tests.
