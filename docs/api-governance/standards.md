# API Governance Standards

## Contract-first development

- Every externally consumable API has an OpenAPI 3.1 contract.
- Contract changes are reviewed alongside implementation changes.
- Examples cover successful and representative error responses.
- Schemas define required fields, formats, bounds, and nullability intentionally.

## HTTP and resource conventions

- Use nouns for resource paths and standard HTTP methods.
- Use plural collection names (for example, `/api/v1/customers`).
- Use appropriate status codes and consistent error bodies.
- Support pagination, filtering, and sorting only with documented semantics.
- Use UTC timestamps in ISO 8601 format.
- Avoid leaking internal database identifiers or implementation details where inappropriate.

## Versioning and compatibility

- Version public API paths as `/api/v1` for this project.
- Prefer additive optional fields and new endpoints for compatible evolution.
- Treat field removal/renaming, type changes, required-field additions, and semantic changes as potentially breaking.
- Run automated contract diff checks in CI.
- Publish deprecation notices and migration guidance before retirement.

## Error model

Adopt a consistent Problem Details-style response (RFC 9457) with stable machine-readable error types. Never expose stack traces, SQL errors, secrets, or internal topology to consumers.

## Security and privacy

- Document authentication and authorization requirements in OpenAPI.
- Validate input and constrain payload sizes.
- Avoid sensitive data in URLs.
- Define data classification and redaction for logs.
- Apply scopes/roles and resource-level authorization.

## Governance workflow

1. Author/update the OpenAPI contract.
2. Run Spectral lint rules.
3. Review security, compatibility, and data exposure.
4. Run schema and contract tests.
5. Approve and publish through the API lifecycle.
6. Monitor usage and errors.
7. Deprecate with consumer communication and a migration window.

## Initial lint rules to implement

- Required API title, version, and description.
- Required operation summary and operationId.
- Consistent path casing and plural resource names.
- Explicit response definitions.
- Security requirements for protected operations.
- No undocumented 4xx/5xx behavior.
- Reusable schemas and parameters where appropriate.
- No server URLs pointing to localhost in published contracts.
