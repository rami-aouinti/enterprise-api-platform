# ADR-0006: Focused iPaaS Connector and Workflow Model

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
Enterprise integrations connect systems with different protocols, payload schemas, authentication models, and reliability characteristics. Embedding one-off integration logic in every domain service would create duplication and tight coupling.

## Decision
Build a focused educational integration runtime with a registry of typed source/target connectors and declarative workflows. A workflow defines a trigger, connector references, input/output schemas, mapping steps, destination action, timeout, retry policy, and execution metadata.

Start with a deliberately small connector set: HTTP webhook/API and a broker-backed connector. Keep connector secrets as references to runtime-injected secret values; never store raw credentials in workflow definitions or execution logs.

Validate incoming payloads, apply explicit mappings, enforce outbound destination policies, and record execution lifecycle with correlation ID, timestamps, status, and redacted diagnostics. Retries are bounded and use backoff; exhausted executions become visible for controlled replay.

The runtime orchestrates connectivity and transformation only. Domain services continue to own business rules and invariants.

## Alternatives considered
1. Point-to-point integration in each service: simple for one integration, but duplicates adapters and inconsistent retries.
2. Full commercial iPaaS: realistic in some enterprises, but outside project scope and may require licenses.
3. General-purpose workflow engine first: powerful, but risks building orchestration infrastructure before validating connector requirements.

## Consequences
**Positive:** reusable connector contracts; consistent execution history; explicit, testable, auditable integration behavior; domain services remain decoupled from external protocols.

**Negative:** connector abstractions and workflow validation add complexity; SSRF, credential handling, retries, and replay require controls; runtime can become a bottleneck if it absorbs business logic.

## Security and operations
Protect against SSRF with destination allow-lists, DNS/IP validation, blocked private/link-local/metadata ranges, redirect controls, and egress restrictions. Apply timeouts, payload limits, schema validation, secret redaction, tenant/consumer authorization, and audit trails. Replay must be authorized and idempotent.

## Review criteria
Revisit the workflow model after implementing the first end-to-end connector pair; simplify abstractions not justified by actual connector behavior.
