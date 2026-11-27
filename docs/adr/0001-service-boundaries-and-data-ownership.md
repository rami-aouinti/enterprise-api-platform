# ADR-0001: Service Boundaries and Data Ownership

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
The platform models customer, policy, claims, and billing capabilities. A shared database or shared domain model would tightly couple services and allow one service to change another service's data without its owner's knowledge.

## Decision
Use business capabilities as the initial service boundaries:
- Customer Service owns customer identity/profile and contact data.
- Policy Service owns policy lifecycle and coverage data.
- Claims Service owns claim intake, assessment state, and claim lifecycle.
- Billing Service owns invoices, payment status, and billing records.

Each service owns its persistence schema and business invariants. Other services access that data through published APIs or integration events, never by querying or writing another service's private tables. Shared packages are limited to technical utilities and published contracts, not a shared domain model.

Cross-service workflows use explicit APIs or asynchronous messages. Distributed transactions are not assumed. Workflows spanning services must model state, timeouts, and recovery explicitly.

## Alternatives considered
1. One modular monolith and one schema: simpler initially, but weaker demonstration of independent service ownership and deployment.
2. Microservices sharing one database: distributed deployment with continued data coupling; rejected.
3. Database-per-service immediately: strongest isolation, but increases local setup and migration complexity. Logical ownership starts immediately; physical isolation can evolve with implementation.

## Consequences
**Positive:** clear ownership and authorization boundaries; independent service evolution; explicit integration contracts.

**Negative:** cross-service reads require APIs/events; eventual consistency and duplicate delivery must be handled; more operational and testing boundaries are required.

## Security and operations
Enforce service-level authorization on every protected operation. Do not share private database credentials across services. Instrument cross-service calls with trace and correlation context. Document data classification and retention as the domain model becomes concrete.

## Review criteria
Revisit boundaries if a capability has no independent business lifecycle, synchronous calls create excessive runtime coupling, or operational overhead outweighs the value.
