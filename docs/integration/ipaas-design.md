# iPaaS-Style Integration Runtime Design

## Objective

Provide a small, auditable integration runtime that connects systems through reusable adapters and declarative workflows. The goal is to demonstrate core iPaaS concepts, not to reproduce a commercial platform.

## Core concepts

- **Connector definition:** supported protocol, configuration schema, authentication method, capabilities, and health check.
- **Connection instance:** environment-specific endpoint and secret reference. Secret values are never stored in plain workflow JSON.
- **Trigger:** webhook, scheduled poll, or broker subscription.
- **Workflow:** ordered set of validated steps with explicit input/output contracts.
- **Mapping:** declarative transformation from source schema to destination schema.
- **Execution:** one run with correlation ID, state, timings, attempts, and redacted error details.
- **Retry policy:** bounded attempts, exponential backoff, jitter, and retryable error classification.
- **Dead-letter state:** terminal failure requiring inspection or replay under controlled permissions.

## Initial vertical slice

1. Receive a signed webhook from a mock CRM.
2. Validate the incoming payload against a JSON Schema.
3. Normalize the source customer record.
4. Apply a declarative field mapping.
5. Call Customer Service through the gateway.
6. Persist execution state and correlation ID.
7. Retry transient errors; stop on permanent validation/authentication errors.
8. Expose execution history and a safe replay operation.

## Connector interface

A connector should declare:
- Stable connector type and version
- Configuration schema
- Supported operations
- Authentication requirements
- Timeout and retry defaults
- Health-check behavior
- Input/output schema references

Keep connector-specific protocol code behind adapters. Workflows should not contain arbitrary PHP, shell commands, or unrestricted expressions.

## Reliability

- Use idempotency keys for destination writes.
- Persist execution state transitions.
- Make retries bounded and observable.
- Use an outbox when a database transaction must reliably trigger message publication.
- Do not promise exactly-once delivery; design for at-least-once delivery plus idempotent processing.

## Security

- Store credentials as secret references.
- Allowlist outbound hosts and protocols.
- Block loopback, link-local, private, and cloud metadata destinations unless explicitly required for a controlled local fixture.
- Redact credentials and sensitive payload fields from logs.
- Apply per-connection authorization and audit changes.

## Non-goals for the first version

- Arbitrary user-written code execution
- Hundreds of production connectors
- Full BPMN compatibility
- Multi-region high availability
- A claim of production-grade SaaS maturity
