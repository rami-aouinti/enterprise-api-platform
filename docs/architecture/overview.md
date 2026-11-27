# Architecture Overview

## Context

The platform demonstrates how an organization can expose and govern APIs while integrating independently owned business systems and external partners. The example domain is insurance-like (customers, policies, claims, billing), but the architecture is generic.

## Logical components

### Experience layer
- **Developer/Admin Portal (React):** API catalog, API lifecycle, consumer applications, integration configuration, execution history, and operational views.
- **External API consumers:** partner applications and internal channels. They never access service internals directly.

### Edge and identity
- **Kong Gateway:** single managed entry point for published APIs; route selection, TLS termination in deployment, request limits, authentication/authorization integration, and gateway telemetry.
- **Keycloak:** identity provider for users and machine clients; issues OAuth2/OIDC tokens. The gateway and services validate tokens according to explicit trust boundaries.

### Domain services
- **Customer Service:** customer profile and contact data.
- **Policy Service:** policy lifecycle and coverage.
- **Claims Service:** claim intake and status.
- **Billing Service:** invoices and payment status.
Each service owns its data and exposes versioned contracts. Cross-service workflows should avoid shared database writes.

### Integration and messaging
- **RabbitMQ:** task/work-queue messaging, command-like asynchronous jobs, retries, and dead-letter queues.
- **Kafka:** append-oriented domain event streams where multiple consumers, replay, and independent offsets are useful.
- **Integration Runtime (iPaaS-style):** connector registry, source/target configuration, mapping, validation, routing, workflow execution, retry policy, and execution history.

### Data and operations
- **PostgreSQL:** service-owned transactional persistence and platform configuration.
- **Redis:** cache only where a measured use case justifies it.
- **OpenTelemetry:** common instrumentation for traces, metrics, and logs.
- **Prometheus/Grafana:** metrics and dashboards.
- **Loki:** centralized logs.
- **Jaeger:** distributed trace exploration.

## Main request flow

1. A consumer obtains a token from Keycloak using an appropriate OAuth2 flow.
2. The consumer calls a published API through Kong.
3. Kong applies routing and configured edge policies.
4. The owning Symfony service validates authorization and business rules.
5. The service reads/writes its own database.
6. For cross-service side effects, it emits an event or queues work using a reliable publication pattern.
7. Consumers process messages idempotently and expose health/metrics/traces.

## Integration flow

1. An operator configures a source connector and destination connector.
2. A trigger (polling, webhook, or message) starts a workflow.
3. The runtime validates and normalizes the source payload.
4. A mapping transforms source fields to the target contract.
5. The runtime invokes the destination adapter or publishes a message.
6. Retries follow bounded backoff rules; exhausted messages go to a dead-letter state.
7. Each execution records correlation identifiers, status, timing, and redacted diagnostics.

## Important boundaries

- The gateway is not a replacement for domain authorization or business validation.
- Keycloak authenticates identities; each API still authorizes actions and scopes.
- Kafka and RabbitMQ are complementary, not interchangeable by default.
- The integration runtime orchestrates connectivity and transformation; domain services remain owners of business invariants.
- Observability must propagate trace/correlation context across HTTP and messaging boundaries.
- No service may depend on another service's private database schema.

## Deployment evolution

Start with Docker Compose for a reproducible local environment. Introduce Kubernetes manifests only after service health, configuration, probes, resource limits, and operational behavior are understood. A local demo is not automatically production-ready.

## Architecture diagrams

The diagrams will evolve alongside implementation. Keep Mermaid diagrams in this directory and update them when flows or ownership boundaries change.
