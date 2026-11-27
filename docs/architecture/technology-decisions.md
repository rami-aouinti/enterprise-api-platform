# Technology Choices and Trade-offs

This document records the initial baseline. Specific versions and deployment modes are finalized in implementation issues and ADRs.

| Technology | Responsibility | Why selected | Trade-off / caution |
|---|---|---|---|
| PHP 8.4 + Symfony 7 | Domain APIs and integration runtime | Aligns with backend experience; mature HTTP, DI, Messenger, validation and security ecosystem | Keep services independently deployable; avoid shared-kernel coupling |
| React + TypeScript | Operator portal | Strong ecosystem and typed UI | Do not put authorization decisions only in the browser |
| Kong Gateway | API edge | Declarative routing and plugin ecosystem | OSS feature set differs from enterprise features; validate OIDC plugin approach |
| Keycloak | Identity provider | Standards-based OAuth2/OIDC and self-hosted learning | Realm/client configuration and key rotation need operational care |
| OpenAPI 3.1 | API contracts | Portable, tool-friendly contract format | Keep contract aligned with runtime behavior |
| Spectral | API linting | Customizable governance rules | Rules should be actionable, not stylistic noise |
| PostgreSQL | Transactional data | Reliable relational persistence | Each service owns schema/data boundaries |
| RabbitMQ | Work queues | Routing, acknowledgements, retry/DLQ patterns | Avoid treating queue delivery as exactly-once |
| Kafka | Event streams | Retention, replay, consumer groups | Operational complexity; use only where stream semantics add value |
| Redis | Cache/coordination | Fast shared cache | Cache invalidation and durability must be explicit |
| OpenTelemetry | Instrumentation | Vendor-neutral telemetry | Cardinality, sampling, and sensitive data require controls |
| Prometheus/Grafana/Loki/Jaeger | Operations | Practical metrics/logs/traces stack | Resource use and retention need limits |
| Docker Compose | Local orchestration | Reproducible developer setup | Not a production orchestrator |
| GitHub Actions | CI | Native repository automation | Pin actions and use least-privilege permissions |

## Deliberate design choices

### RabbitMQ and Kafka
Use RabbitMQ for work distribution and command/task processing with acknowledgements, bounded retries, and dead-lettering. Use Kafka for durable domain event streams where multiple independent consumers and replay are meaningful. Do not duplicate every event to both brokers without a documented business need.

### Gateway and service security
The gateway performs edge controls, but services remain responsible for resource-level authorization and business invariants. A valid JWT does not automatically grant access to every object.

### iPaaS scope
Build a focused educational integration runtime rather than attempting to recreate a commercial iPaaS product. Start with a small number of connector types, explicit mappings, bounded workflows, and auditable execution history.

### API versioning
Prefer additive, backward-compatible changes within a major version. Introduce a new major version only when compatibility cannot be preserved. Publish deprecation and sunset expectations.
