# ADR-0003: Keycloak for OAuth 2.0 and OpenID Connect

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
The platform needs a standards-based identity provider for human users, the developer portal, and machine-to-machine API consumers. Authentication must be separated from application-specific authorization.

## Decision
Use Keycloak as the self-hosted identity provider and authorization server for the local reference environment. Use OpenID Connect for interactive user authentication and OAuth 2.0 client credentials for appropriate machine-to-machine integrations.

Define separate clients for the portal and API consumers. Use narrowly scoped roles/scopes and audiences. Services independently validate access-token signature, issuer, audience, expiry, and permitted algorithms. Services map validated claims to application permissions and enforce resource-level access.

Do not use the password grant. Do not treat ID tokens as API access tokens. Confidential-client secrets are injected at runtime and never committed.

## Alternatives considered
1. Application-managed users and JWTs: less infrastructure, but duplicates identity lifecycle and token security.
2. External managed identity provider: realistic for production, but adds external account dependencies and may incur cost.
3. Other self-hosted identity providers: viable; Keycloak offers a broad OAuth2/OIDC feature set and practical local environment.

## Consequences
**Positive:** standards-based login and machine identity; centralized user/client lifecycle and signing-key management; realistic token validation scenarios.

**Negative:** realm/client configuration adds complexity; key rotation and callback URLs require care; gateway and service validation must remain consistent.

## Security and operations
Use Authorization Code with PKCE for browser-based public clients. Keep access tokens short-lived and configure refresh-token behavior deliberately. Validate issuer, audience, signature, expiry, not-before when present, and algorithm allow-lists. Never log tokens. Plan for signing-key rotation and identity-provider unavailability.

## Review criteria
Revisit the client and flow model when portal, external partner, and service-to-service use cases are implemented and their trust boundaries are testable.
