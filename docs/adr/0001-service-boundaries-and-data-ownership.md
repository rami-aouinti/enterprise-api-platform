# ADR-0001: Service Boundaries and Data Ownership

- **Status:** Accepted
- **Date:** 2026-11-27
- **Related issues:** #12, #17

## Context
The platform models customer, policy, claims, and billing capabilities. A shared database or shared domain model would tightly couple services and allow one service to change another service's data without its owner's knowledge.

## Decision
Use business capabilities as the initial service boundaries:
- Customer Service owns customer identity/profile and contact data.
- Policy Service owns policy lifecycle, insured risks, coverage selections, and policy terms.
- Claims Service owns claim intake, evidence references, assessment, decisions, and claim lifecycle.
- Billing Service owns invoices, payment allocations, payment status, and billing records.

Each service owns its business rules, schema, migrations, database credentials, and persistence lifecycle. The target deployment uses a separate database per service. During local development, services may share a PostgreSQL server instance, but must use separate databases and credentials; sharing a server is an infrastructure choice, not shared data ownership.

Other services access owned data only through versioned APIs or published integration events. Direct reads/writes against another service's tables, shared ORM entities, and cross-service foreign keys are prohibited. Shared packages are limited to technical utilities and versioned contracts, not shared domain models.

Cross-service workflows use explicit APIs or asynchronous messages. Distributed transactions are not assumed. Workflows spanning services must model state, timeouts, retries, idempotency, and recovery explicitly.

## Alternatives considered
1. One modular monolith and one schema: simpler initially, but weaker demonstration of independent service ownership and deployment.
2. Microservices sharing one database/schema: distributed deployment with continued data coupling; rejected.
3. Database-per-service on separate PostgreSQL server instances: strongest infrastructure isolation, but unnecessary operational overhead for the local learning environment. Separate databases and credentials on one local server are sufficient initially.

## Consequences
**Positive:** clear ownership and authorization boundaries; independent service evolution; explicit integration contracts; independent schema migrations and backup/restore boundaries.

**Negative:** cross-service reads require APIs/events; eventual consistency and duplicate delivery must be handled; more operational and testing boundaries are required.

## Security and operations
Enforce service-level authorization on every protected operation. Each service receives credentials only for its own database. Never expose database credentials to another service. Propagate trace and correlation context across HTTP and messaging boundaries. Minimize personal and payment data in events; publish identifiers and business facts rather than entire records.

## Review criteria
Revisit boundaries if a capability has no independent business lifecycle, synchronous calls create excessive runtime coupling, or operational overhead outweighs the value.
