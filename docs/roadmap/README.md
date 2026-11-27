# Roadmap and Delivery Phases

Each phase has a GitHub feature issue and implementation tickets. Complete acceptance criteria and dependencies before moving to the next phase.

## Phase index

| Phase | Feature issue | Implementation tickets |
|---|---|---|
| 00 — Project foundation | [#1](https://github.com/rami-aouinti/enterprise-api-platform/issues/1) | [#12 ADRs](https://github.com/rami-aouinti/enterprise-api-platform/issues/12), [#13 workflow/docs](https://github.com/rami-aouinti/enterprise-api-platform/issues/13) |
| 01 — Runtime foundation | [#2](https://github.com/rami-aouinti/enterprise-api-platform/issues/2) | [#14 Docker Compose](https://github.com/rami-aouinti/enterprise-api-platform/issues/14), [#15 Symfony + CI](https://github.com/rami-aouinti/enterprise-api-platform/issues/15) |
| 02 — Domain APIs | [#3](https://github.com/rami-aouinti/enterprise-api-platform/issues/3) | [#16 Customer API](https://github.com/rami-aouinti/enterprise-api-platform/issues/16), [#17 service boundaries](https://github.com/rami-aouinti/enterprise-api-platform/issues/17) |
| 03 — Identity and API Gateway | [#4](https://github.com/rami-aouinti/enterprise-api-platform/issues/4) | [#18 Keycloak](https://github.com/rami-aouinti/enterprise-api-platform/issues/18), [#19 Kong](https://github.com/rami-aouinti/enterprise-api-platform/issues/19) |
| 04 — API catalog and governance | [#5](https://github.com/rami-aouinti/enterprise-api-platform/issues/5) | [#20 API registry](https://github.com/rami-aouinti/enterprise-api-platform/issues/20), [#21 Spectral + compatibility](https://github.com/rami-aouinti/enterprise-api-platform/issues/21) |
| 05 — Messaging and events | [#6](https://github.com/rami-aouinti/enterprise-api-platform/issues/6) | [#22 RabbitMQ](https://github.com/rami-aouinti/enterprise-api-platform/issues/22), [#23 Kafka + outbox](https://github.com/rami-aouinti/enterprise-api-platform/issues/23) |
| 06 — iPaaS integration runtime | [#7](https://github.com/rami-aouinti/enterprise-api-platform/issues/7) | [#24 connector registry](https://github.com/rami-aouinti/enterprise-api-platform/issues/24), [#25 workflow runtime](https://github.com/rami-aouinti/enterprise-api-platform/issues/25) |
| 07 — Developer and admin portal | [#8](https://github.com/rami-aouinti/enterprise-api-platform/issues/8) | [#26 portal + catalog](https://github.com/rami-aouinti/enterprise-api-platform/issues/26), [#27 administration](https://github.com/rami-aouinti/enterprise-api-platform/issues/27) |
| 08 — Security hardening | [#9](https://github.com/rami-aouinti/enterprise-api-platform/issues/9) | [#28 authorization/SSRF](https://github.com/rami-aouinti/enterprise-api-platform/issues/28), [#29 secrets/audit/scanning](https://github.com/rami-aouinti/enterprise-api-platform/issues/29) |
| 09 — Observability and performance | [#10](https://github.com/rami-aouinti/enterprise-api-platform/issues/10) | [#30 OpenTelemetry](https://github.com/rami-aouinti/enterprise-api-platform/issues/30), [#31 dashboards + k6](https://github.com/rami-aouinti/enterprise-api-platform/issues/31) |
| 10 — Delivery, deployment and portfolio readiness | [#11](https://github.com/rami-aouinti/enterprise-api-platform/issues/11) | [#35 CI images + Kubernetes](https://github.com/rami-aouinti/enterprise-api-platform/issues/35), [#36 end-to-end demo](https://github.com/rami-aouinti/enterprise-api-platform/issues/36) |

## Phase outcomes

| Phase | Main outcomes |
|---|---|
| 00 | Documentation, repo conventions, architecture decisions, issue workflow |
| 01 | Docker Compose, Symfony skeleton, PostgreSQL, health checks, CI |
| 02 | Customer, policy, claims, billing APIs and OpenAPI contracts |
| 03 | Keycloak, Kong, OAuth2/OIDC, JWT validation, rate limiting |
| 04 | Catalog, lifecycle, style rules, breaking-change checks |
| 05 | RabbitMQ, Kafka, outbox, idempotent consumers, retries/DLQ |
| 06 | Connectors, mappings, workflow execution, audit trail |
| 07 | React portal for APIs, consumers, integrations, and execution history |
| 08 | Threat model, abuse controls, secrets, audit, security tests |
| 09 | OpenTelemetry, metrics/logs/traces, dashboards, load tests |
| 10 | CI/CD, container hardening, Kubernetes deployment, end-to-end demo, architecture docs, ADRs, interview notes |

## Phase exit criteria

A phase is complete only when:
- Its feature issue acceptance criteria are met.
- All child implementation tickets are closed or explicitly deferred.
- Tests and quality gates pass.
- Documentation and diagrams reflect the actual implementation.
- A demo or reproducible verification procedure exists.
- Known limitations and next steps are recorded.

## Suggested order

Follow phases sequentially for foundational dependencies. Some documentation and UI design tasks can proceed in parallel, but do not build portal features against unstable contracts or integrate gateway policies before the identity model is agreed.

## Scope discipline

The goal is depth and defensibility, not a large list of technologies. Prefer a working, tested vertical slice over superficial integrations. If a tool adds no meaningful learning or business value, record the decision not to use it.

## Project management note

Feature issues are phase-level epics; task issues are implementation work. The available GitHub integration did not expose operations to create repository labels, milestones, or a Projects-v2 board. The phase/task hierarchy is therefore linked here and in issue bodies; no labels, milestones, or project board are claimed as configured.