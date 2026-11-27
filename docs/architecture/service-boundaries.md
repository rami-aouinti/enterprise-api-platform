# Service Boundaries and Domain Context Map

**Status:** Baseline for Phase 02  
**Related work:** Issue #17, ADR-0001

This document defines ownership and integration rules before Policy, Claims, and Billing APIs are implemented. The Customer API is the first implemented domain API; the other services below are architectural boundaries, not claims that their runtime implementations already exist.

## 1. Bounded contexts and ownership

| Service | Owns | Must not own |
|---|---|---|
| Customer | Customer identity, name, contact details, customer lifecycle | Policies, claims, invoices, payment transactions |
| Policy | Policy number, product, insured risk, coverage, premium terms, effective dates, policy lifecycle | Customer profile, claim assessment, invoice/payment ledger |
| Claims | Claim reference, loss details, evidence metadata/references, assessment, decision, claim lifecycle | Policy terms, customer profile, invoice/payment ledger |
| Billing | Invoice, invoice lines, amount due, payment allocation, payment status, billing ledger | Customer profile, policy coverage, claim assessment |

A service is the authoritative writer for its owned data. Other services may keep purpose-limited read projections, but those projections are not authoritative and must be rebuildable from APIs/events.

## 2. Context map

~~~mermaid
flowchart LR
    Consumer[Partner / Channel] --> Gateway[Kong API Gateway]
    Gateway --> Customer[Customer Service]
    Gateway --> Policy[Policy Service]
    Gateway --> Claims[Claims Service]
    Gateway --> Billing[Billing Service]

    Policy -->|Customer API: verify/reference| Customer
    Claims -->|Policy API: coverage verification| Policy
    Claims -->|Customer API: contact reference when needed| Customer
    Billing -->|Customer API: invoice customer reference| Customer

    Policy -.->|PolicyIssued / PolicyCancelled| Events[(Domain Events)]
    Claims -.->|ClaimOpened / ClaimDecisionRecorded| Events
    Billing -.->|InvoiceIssued / PaymentReceived| Events
    Events -.-> Customer
    Events -.-> Policy
    Events -.-> Claims
    Events -.-> Billing
~~~

Solid arrows represent synchronous API calls. Dashed arrows represent asynchronous domain-event publication/consumption. The event broker is transport, not an owner of domain state. The gateway is an entry point and does not orchestrate business rules.

## 3. Allowed interaction rules

1. A service may read or change its own database only.
2. A service obtains another context's current authoritative facts through that context's published API.
3. A service reacts to another context's durable business fact through a versioned event contract.
4. Never join across service databases, query another service's tables, or write another service's records.
5. Avoid chatty synchronous chains. If a workflow can tolerate eventual consistency, prefer events and a local process state.
6. Every event has a stable event ID, event type, schema version, occurrence timestamp, producer, and correlation ID. Consumers must be idempotent.
7. Events carry the minimum required business data. Prefer aggregate identifiers and immutable facts over full customer, policy, or payment records.
8. Synchronous APIs have explicit timeouts and failure behavior. A downstream outage must not silently produce a successful business outcome.

## 4. Initial integration contracts

| Producer | Contract | Consumer | Interaction | Purpose |
|---|---|---|---|---|
| Customer | GET /api/v1/customers/{id} | Policy, Claims, Billing | Synchronous REST | Validate/reference a customer; return only fields needed by the caller |
| Policy | GET /api/v1/policies/{id}/coverage | Claims | Synchronous REST | Verify coverage for the reported loss date and risk |
| Policy | policy.issued.v1 / policy.cancelled.v1 | Claims, Billing | Event | Notify lifecycle changes without shared database access |
| Claims | claim.opened.v1 / claim.decision-recorded.v1 | Policy, Billing | Event | Publish claim lifecycle facts and decisions |
| Billing | invoice.issued.v1 / payment.received.v1 | Authorized consumers | Event/API | Publish billing lifecycle facts |

These are logical contract names, not implemented endpoints or broker bindings yet. Detailed OpenAPI schemas and AsyncAPI/event schemas are added with the owning service and messaging tickets. No consumer may infer an undocumented contract from another service's database model.

## 5. Cross-service workflow examples

### Claim intake
1. Claims receives a claim request and validates its own request shape and authorization.
2. Claims calls Policy's coverage endpoint with the policy reference and loss date.
3. Claims stores the claim and its own assessment state in the Claims database.
4. Claims publishes claim.opened.v1 after its state is committed.
5. Billing may consume the event to create a pending billing/review task where business rules require it. It does not update Claims tables.

If Policy is unavailable, Claims returns a retryable dependency failure or accepts the claim in an explicit pending_policy_verification state. It must not assume coverage.

### Policy issuance
1. Policy verifies the customer reference through Customer API.
2. Policy validates product and coverage rules within Policy.
3. Policy persists the policy in its own database.
4. Policy publishes policy.issued.v1 after commit.
5. Billing consumes the event and creates its own invoice record according to billing rules.

The workflow is eventually consistent. It does not use a distributed database transaction.

## 6. Persistence boundaries

| Service | Logical database | Schema owner | Credentials |
|---|---|---|---|
| Customer | customer_service | Customer | Customer-only role |
| Policy | policy_service | Policy | Policy-only role |
| Claims | claims_service | Claims | Claims-only role |
| Billing | billing_service | Billing | Billing-only role |

For local Docker Compose, these databases may run on one PostgreSQL server/container to reduce resource usage. They remain separate databases with separate users/credentials and independent migrations. Production may place them on separate database instances without changing service contracts.

The currently implemented Customer API migration is owned by Customer Service. Policy, Claims, and Billing databases are created by their respective implementation tickets; this document does not imply they already exist.

## 7. Failure, consistency, and security rules

- Use an outbox when a service must reliably publish an event corresponding to a committed database change.
- Consumers persist processed event IDs or otherwise enforce idempotency.
- Retry transient failures with bounded exponential backoff; route exhausted messages to a dead-letter path.
- Use correlation IDs across HTTP and messaging; do not use them as authorization credentials.
- Authenticate service-to-service calls and authorize the requested operation at the receiving service.
- Do not put secrets, access tokens, full payment details, or unnecessary personal data in events or logs.
- Define retention and deletion propagation when the concrete data classification is implemented.

## 8. Implementation status

| Capability | Status |
|---|---|
| Customer API and Customer-owned PostgreSQL persistence | Implemented in Ticket #16 |
| Policy boundary, ownership, and contracts | Defined; implementation pending |
| Claims boundary, ownership, and contracts | Defined; implementation pending |
| Billing boundary, ownership, and contracts | Defined; implementation pending |
| Separate local databases/users for all four services | Target architecture; provisioned with service infrastructure tickets |
| Versioned event schemas and broker topology | Defined conceptually; implemented in messaging tickets |

## 9. Validation checklist

- [x] Context map identifies ownership and allowed interactions.
- [x] Each service has a separately owned persistence boundary in the target design.
- [x] Cross-service workflows use APIs/events rather than shared database writes.
- [x] Initial domain and integration contracts are documented.
- [ ] Physical databases/users for Policy, Claims, and Billing are provisioned with their service implementations.
- [ ] OpenAPI and event schemas are added alongside the owning API/messaging implementations.
