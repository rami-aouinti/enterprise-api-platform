# Architecture Decision Records

Architecture Decision Records (ADRs) capture decisions that materially affect architecture, security, operations, and developer workflow. Each record includes context, decision, alternatives, consequences, and security/operational implications.

## Status policy

- **Proposed:** selected direction documented, pending validation during implementation or explicit review.
- **Accepted:** reviewed and approved for implementation.
- **Superseded:** replaced by a later ADR; retain the historical record.
- **Rejected:** considered but not selected.

Do not silently rewrite an accepted decision. Create a new ADR and link the superseded record when the architecture changes.

## Decision index

| ADR | Topic | Status |
|---|---|---|
| [ADR-0001](0001-service-boundaries-and-data-ownership.md) | Service boundaries and data ownership | Proposed |
| [ADR-0002](0002-kong-api-gateway.md) | Kong as API Gateway | Proposed |
| [ADR-0003](0003-keycloak-oauth2-oidc-identity.md) | Keycloak and OAuth2/OIDC identity | Proposed |
| [ADR-0004](0004-rabbitmq-kafka-responsibilities.md) | RabbitMQ vs Kafka responsibilities | Proposed |
| [ADR-0005](0005-api-versioning-and-compatibility.md) | API versioning and compatibility | Proposed |
| [ADR-0006](0006-ipaas-connector-and-workflow-model.md) | iPaaS connector and workflow model | Proposed |
| [ADR-0007](0007-opentelemetry-observability.md) | OpenTelemetry observability | Proposed |

## Review workflow

1. Review context, alternatives, and consequences with the related implementation issue.
2. Validate assumptions against the actual selected tool edition and implementation constraints.
3. Change status to Accepted only after review.
4. Link implementation issues, pull requests, and follow-up ADRs.
5. Revisit decisions when measurable evidence or new requirements justify a change.

## Template

New ADRs should contain: title and number, status, date, related issue, context, decision, alternatives considered, consequences, security and operational implications, and review criteria.
