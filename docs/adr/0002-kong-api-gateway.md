# ADR-0002: Kong as the API Gateway

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
Published APIs need a consistent entry point for routing, deployment-level TLS termination, request limits, and edge security controls. Direct public access to every service would duplicate policy and increase the exposed attack surface.

## Decision
Use Kong Gateway as the north-south API entry point. Keep gateway configuration declarative and version-controlled. Kong handles routing, edge request limits, configured authentication integration, and gateway telemetry.

Domain services remain responsible for business validation, resource-level authorization, and ownership checks. Passing the gateway does not make a request trusted by a service.

The initial local setup uses the open-source distribution and only capabilities verified to exist in that edition. Any OIDC integration requiring a paid plugin or external component must be evaluated before adoption.

## Alternatives considered
1. No gateway: simpler, but edge policies become inconsistent and service exposure is harder to manage.
2. Custom Symfony gateway: flexible, but duplicates mature gateway functionality and creates avoidable maintenance.
3. Traefik or NGINX: viable routing options, but Kong's API-oriented plugin and declarative configuration model better supports the learning goals.

## Consequences
**Positive:** centralized routing and edge policies; clear separation of edge and domain responsibilities; reviewable configuration as code.

**Negative:** another component to operate; plugin availability differs by edition; gateway and service policies can drift unless tested together.

## Security and operations
Apply conservative body/header limits, timeouts, and rate limits. Never log authorization headers or sensitive payloads. Validate forwarded headers and trusted proxy settings. Keep service endpoints inaccessible from untrusted networks in deployed environments. Maintain health checks and gateway metrics.

## Review criteria
Revisit if required OIDC capabilities are unavailable in the selected edition, gateway configuration becomes a bottleneck, or a simpler proxy meets the requirements with less complexity.
