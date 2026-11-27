# Architecture Decision Records

Record decisions that materially affect architecture, security, operations, or developer workflow.

## Format

Each ADR should contain:
- Number and title
- Status: Proposed, Accepted, Superseded, or Rejected
- Context and problem
- Decision
- Alternatives considered
- Consequences (positive and negative)
- Security/operational implications
- Links to related issues and implementation

## Planned decisions

| ADR | Topic | Initial status |
|---|---|---|
| ADR-0001 | Service boundaries and data ownership | Proposed |
| ADR-0002 | Kong as API Gateway | Proposed |
| ADR-0003 | Keycloak and OAuth2/OIDC flows | Proposed |
| ADR-0004 | RabbitMQ vs Kafka responsibilities | Proposed |
| ADR-0005 | API versioning and compatibility policy | Proposed |
| ADR-0006 | iPaaS connector and workflow model | Proposed |
| ADR-0007 | Observability with OpenTelemetry | Proposed |

Create one ADR per decision as the implementation makes the trade-offs concrete. Do not mark a decision Accepted until the alternatives and consequences have been reviewed.
