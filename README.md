# Enterprise API Integration Platform

A production-minded learning project demonstrating enterprise API management, API governance, secure service integration, event-driven architecture, and an extensible iPaaS-style integration runtime.

> **Purpose:** build and explain a realistic platform relevant to a Senior API Developer role. This is an independent portfolio project, not a DEVK system.

## Current implementation

- Symfony 7.4 / PHP 8.4 customer API.
- PostgreSQL 17 with persistent Docker volume.
- Keycloak 26.3.3 with a reproducible local `enterprise-platform` realm.
- OIDC Authorization Code + PKCE for the public portal client.
- OAuth 2.0 Client Credentials for the confidential machine client.
- Realm roles, API audience, five-minute access tokens, and JWKS rotation guidance.
- Docker Compose local runtime and container health checks.
- Liveness endpoint: `GET /health`.
- Readiness endpoint: `GET /health/ready` (executes `SELECT 1` against PostgreSQL).
- PHPUnit unit and functional test suites.
- PHPStan static analysis and PHP-CS-Fixer code style.
- GitHub Actions backend quality workflow.

## Start locally

Requirements: Git, Docker Engine/Desktop, Docker Compose v2.

~~~bash
cp .env.example .env
# Replace every placeholder in .env with private local values.
docker compose config --quiet
docker compose up -d --build
docker compose ps
curl --fail http://localhost:8081/health/ready
sh scripts/keycloak-provision.sh
sh scripts/keycloak-token-smoke-test.sh
~~~

See [Keycloak identity flows](docs/security/keycloak-identity.md) for PKCE, Client Credentials, token validation, and key rotation.

Verify the API endpoints:

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
bash scripts/smoke-test.sh
~~~

## Backend quality commands

The API container includes Composer and the project dependencies:

~~~bash
docker compose exec api php bin/console about
docker compose exec api composer test
docker compose exec api composer test:unit
docker compose exec api composer test:functional
docker compose exec api composer analyse
docker compose exec api composer cs:check
~~~

The first dependency resolution creates `composer.lock` inside the container. Copy it to the repository and commit it so local builds and CI use the same exact dependency versions:

~~~bash
docker compose cp api:/app/composer.lock ./composer.lock
git add composer.lock
git commit -m "chore(deps): lock Symfony backend dependencies (#15)"
git push origin main
~~~

## Useful Docker commands

~~~bash
docker compose logs -f
docker compose logs -f api
docker compose logs -f database
docker compose logs -f keycloak
docker compose down
docker compose up -d
~~~

**Destructive reset:** this deletes the PostgreSQL and Keycloak volumes and all local data.

~~~bash
docker compose down -v
docker compose up -d --build
~~~

The API is exposed on 127.0.0.1:8000, PostgreSQL on 127.0.0.1:5432, and Keycloak on 127.0.0.1:8081. The database is reachable from the API container at database:5432. Use synthetic data only.

## Planned technology baseline

| Concern | Initial choice |
|---|---|
| Backend services | PHP 8.4, Symfony 7.4 |
| Admin portal | React, TypeScript, Vite |
| API Gateway | Kong Gateway (OSS) |
| Identity | Keycloak (OAuth 2.0 / OIDC) |
| API contract | OpenAPI 3.1 |
| API linting/governance | Spectral |
| Relational data | PostgreSQL |
| Messaging | RabbitMQ |
| Event streaming | Apache Kafka |
| Cache | Redis, only where justified |
| Observability | OpenTelemetry, Prometheus, Grafana, Loki, Jaeger |
| Local runtime | Docker Compose |
| Delivery | GitHub Actions |

This is a practical reference architecture, not a vendor requirement. Versions and design decisions are documented in [ADRs](docs/adr/README.md).

## Architecture at a glance

React Developer/Admin Portal → Kong Gateway → Symfony domain APIs

Supporting capabilities:
- Keycloak provides human and machine identity and OAuth2/OIDC token issuance.
- PostgreSQL stores service-owned transactional data.
- RabbitMQ handles task-oriented asynchronous messaging.
- Kafka carries durable domain event streams where replay or multiple consumers matter.
- The integration runtime connects systems through adapters, transformations, routing, and execution history.
- OpenTelemetry instruments services; Prometheus/Grafana, Loki, and Jaeger provide operational visibility.

See [Architecture](docs/architecture/overview.md), [Technology decisions](docs/architecture/technology-decisions.md), and [Roadmap](docs/roadmap/README.md).

## Documentation

- [Project Handbook](docs/PROJECT-HANDBOOK.md)
- [Contributing Guide](CONTRIBUTING.md) — direct-to-main workflow.
- [Development Workflow](docs/development/workflow.md)
- [Local Setup](docs/development/local-setup.md)
- [Repository Structure](docs/development/repository-structure.md)
- [Architecture Overview](docs/architecture/overview.md)
- [Service Boundaries](docs/architecture/service-boundaries.md)
- [Keycloak Identity Flows](docs/security/keycloak-identity.md)
- [Technology decisions](docs/architecture/technology-decisions.md)
- [Architecture Decision Records](docs/adr/README.md)
- [API Governance](docs/api-governance/standards.md)
- [Security Threat Model](docs/security/threat-model.md)
- [iPaaS Design](docs/integration/ipaas-design.md)
- [Observability](docs/operations/observability.md)
- [Roadmap](docs/roadmap/README.md)

## Delivery approach

We work directly on `main` for this repository. Do not create branches or pull requests. Work on one ticket at a time, use focused Conventional Commit messages with the issue number, validate changes, and document actual test results.

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
├── packages/shared-contracts/
├── infrastructure/
│   ├── docker/
│   ├── keycloak/
│   ├── kong/
│   ├── observability/
│   └── kubernetes/
└── docs/
~~~

The structure is a target, not a claim that every listed service already exists.

## Status

**Phase 01 — Runtime foundation:** Docker Compose and the Symfony backend bootstrap are committed. Phase 02 defines the initial domain boundaries and Customer API. Phase 03 introduces identity and gateway enforcement.

## Learning outcomes

The completed project will provide concrete code and tests to explain API design and governance, gateway responsibilities, OAuth2/OIDC, threat protection, synchronous vs asynchronous integration, delivery guarantees, iPaaS connector design, observability, and operational trade-offs.
