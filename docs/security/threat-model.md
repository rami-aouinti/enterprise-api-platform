# Security Threat Model

## Scope

The initial threat model covers external consumers, the API Gateway, Keycloak, Symfony services, message brokers, connector credentials, integration payloads, and operational telemetry.

## Assets

- User and machine identities
- OAuth2 access and refresh tokens
- API keys and connector credentials
- Customer/policy/claim sample data
- API contracts and configuration
- Message payloads and integration execution history
- Signing keys, database credentials, and infrastructure secrets

## Trust boundaries

1. Internet/client to gateway
2. Gateway to internal service
3. Service to identity provider
4. Service to database
5. Service to broker and broker consumers
6. Integration runtime to third-party connector
7. Operator portal to management APIs

## Threats and controls

| Threat | Example | Planned controls |
|---|---|---|
| Broken authentication | Forged, expired, or wrong-audience JWT | Signature, issuer, audience, expiry and algorithm validation; key rotation |
| Broken authorization | User accesses another customer's record | Resource-level authorization and negative tests |
| Abuse/resource exhaustion | Burst traffic or huge payload | Rate limits, quotas, body limits, timeouts |
| Injection | Malicious query or mapping expression | Parameterized queries, safe parsers, allowlisted mapping operations |
| SSRF | Connector calls internal metadata endpoints | Destination allowlists, DNS/IP checks, egress restrictions |
| Secret leakage | Token appears in logs | Redaction, secret manager, log scanning |
| Replay/duplicate delivery | Message processed more than once | Idempotency keys and deduplication |
| Message loss | DB commit succeeds but event publish fails | Transactional outbox and retry/reconciliation |
| Supply-chain risk | Vulnerable dependency or action | Dependency scanning, pinned actions, update policy |
| Sensitive telemetry | PII in traces/logs | Data minimization, redaction, retention controls |

## OAuth2/OIDC guidance

- Use Authorization Code with PKCE for browser-based user sign-in.
- Use Client Credentials for machine-to-machine access where appropriate.
- Do not use the Resource Owner Password Credentials grant.
- Keep access tokens short-lived and validate audience and scopes.
- Keep client secrets out of frontend bundles.
- Use asymmetric signing and documented key rotation where supported.

## Security test plan

- Missing, malformed, expired, wrong-issuer, and wrong-audience token tests.
- Insufficient scope/role and cross-tenant/resource access tests.
- Rate-limit and payload-size tests.
- Connector URL allowlist and SSRF regression tests.
- Secret-redaction tests.
- Replay and duplicate event tests.
- Dependency and container image scanning.

This is a living threat model. Revisit it when new connectors, data types, or trust boundaries are introduced.
