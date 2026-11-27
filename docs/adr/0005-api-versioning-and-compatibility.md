# ADR-0005: API Versioning and Compatibility Policy

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
APIs are consumed by independently deployed clients and services. Uncontrolled contract changes can break consumers and make integrations difficult to evolve.

## Decision
Publish REST contracts using OpenAPI 3.1 and keep the contract under version control alongside implementation.

Prefer additive, backward-compatible changes within a major API version. Use an explicit major version in the URL path, for example /api/v1, when a breaking contract change is unavoidable. Treat removing or renaming fields, changing field types, tightening validation, and changing response semantics as potentially breaking.

For every breaking version, document migration guidance, a deprecation window, and a sunset date. Run OpenAPI linting and compatibility checks in CI. A document parsing successfully does not by itself prove compatibility.

## Alternatives considered
1. No explicit versioning: clean URLs, but breaking changes become difficult to coordinate.
2. Header/media-type versioning: flexible, but less discoverable and more complex for this learning platform.
3. Version every minor change: rejected because it creates unnecessary parallel contracts.

## Consequences
**Positive:** consumers can identify contract versions; additive evolution is encouraged; breaking changes are visible and reviewable.

**Negative:** multiple major versions may need temporary support; deprecation and compatibility checks add process and CI work.

## Security and operations
Validate request and response schemas where appropriate. Avoid leaking internal error details. Deprecated endpoints remain subject to authentication, authorization, rate-limit, and telemetry policies.

## Review criteria
Revisit versioning if non-REST interfaces or external partner constraints require a different contract strategy.
