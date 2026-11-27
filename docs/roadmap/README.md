# Roadmap and Delivery Phases

The roadmap is issue-driven. Each phase has one GitHub feature/epic issue plus implementation tickets. Complete acceptance criteria and dependencies before moving to the next phase.

| Phase | Feature | Main outcomes |
|---|---|---|
| 0 | Project foundation | Docs, repo conventions, architecture decisions, issue workflow |
| 1 | Runtime foundation | Docker Compose, Symfony skeleton, PostgreSQL, health checks, CI |
| 2 | Domain APIs | Customer, policy, claims, billing APIs and OpenAPI contracts |
| 3 | Identity and gateway | Keycloak, Kong, OAuth2/OIDC, JWT validation, rate limiting |
| 4 | API catalog and governance | Catalog, lifecycle, style rules, breaking-change checks |
| 5 | Messaging and events | RabbitMQ, Kafka, outbox, idempotent consumers, retries/DLQ |
| 6 | iPaaS integration runtime | Connectors, mappings, workflow execution, audit trail |
| 7 | Developer portal | React portal for APIs, consumers, integrations, and execution history |
| 8 | Security hardening | Threat model, abuse controls, secrets, audit, security tests |
| 9 | Observability and performance | OpenTelemetry, metrics/logs/traces, dashboards, load tests |
| 10 | Delivery and deployment | CI/CD, container hardening, Kubernetes deployment, runbooks |
| 11 | Portfolio and interview readiness | End-to-end demo, architecture docs, ADRs, interview notes |

## Phase exit criteria

A phase is complete only when:
- Its feature issue acceptance criteria are met.
- All child implementation tickets are closed or explicitly deferred.
- Tests and quality gates pass.
- Documentation and diagrams reflect the actual implementation.
- A demo or reproducible verification procedure exists.
- Known limitations and next steps are recorded.

## Suggested order

Follow the phases sequentially for foundational dependencies. Some documentation and UI design tasks can proceed in parallel, but do not build portal features against unstable contracts or integrate gateway policies before the identity model is agreed.

## Scope discipline

The goal is depth and defensibility, not a large list of technologies. Prefer a working, tested vertical slice over superficial integrations. If a tool adds no meaningful learning or business value, record the decision not to use it.
