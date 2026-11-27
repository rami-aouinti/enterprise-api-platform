# ADR-0003: Keycloak for OAuth 2.0 and OpenID Connect

- **Status:** Accepted
- **Date:** 2026-11-27
- **Related issue:** [#18](https://github.com/rami-aouinti/enterprise-api-platform/issues/18)

## Context
The platform needs a standards-based identity provider for human users, the developer portal, and machine-to-machine API consumers. Authentication must be separated from application-specific authorization.

## Decision
Use Keycloak as the self-hosted identity provider and authorization server for the local reference environment. Use OpenID Connect Authorization Code with PKCE (S256) for browser-based public clients and OAuth 2.0 Client Credentials for confidential machine clients.

The local `enterprise-platform` realm is imported from version-controlled configuration. It provisions a public `enterprise-portal` client, an `enterprise-api` resource-server audience, a confidential `enterprise-api-machine` client, synthetic test user, realm roles, and an API audience mapper. The machine-client secret is injected from the ignored local environment file and applied after realm import; it is not part of the realm export.

Define separate clients for the portal and API consumers. Use narrowly scoped roles/scopes and audiences. Services independently validate access-token signature, issuer, audience, expiry, not-before when present, and permitted algorithms. Services map validated claims to application permissions and enforce resource-level access.

Do not use the password grant or implicit flow. Do not treat ID tokens as API access tokens. Confidential-client secrets are injected at runtime and never committed or exposed in frontend assets.

## Alternatives considered
1. Application-managed users and JWTs: less infrastructure, but duplicates identity lifecycle and token security.
2. External managed identity provider: realistic for production, but adds external account dependencies and may incur cost.
3. Other self-hosted identity providers: viable; Keycloak offers a broad OAuth2/OIDC feature set and practical local environment.

## Consequences
**Positive:** standards-based login and machine identity; centralized user/client lifecycle and signing-key management; reproducible local identity configuration and token-flow demonstrations.

**Negative:** realm/client configuration adds complexity; callback URLs and key rotation require care; gateway and service validation must remain consistent.

## Security and operations
Use Authorization Code with PKCE for browser-based public clients. Keep access tokens short-lived and configure refresh-token behavior deliberately. Validate issuer, audience, signature, expiry, not-before when present, and algorithm allow-lists. Never log tokens. Plan for signing-key rotation and identity-provider unavailability.

The local realm uses five-minute access tokens, refresh-token rotation for interactive sessions, and a JWKS endpoint for public-key distribution. The local development realm uses HTTP and synthetic credentials only; production deployments require TLS, managed secrets, hardened Keycloak configuration, and explicit redirect origins.

## Review criteria
Revisit the client and flow model when portal, external partner, and service-to-service use cases are implemented and their trust boundaries are testable.
