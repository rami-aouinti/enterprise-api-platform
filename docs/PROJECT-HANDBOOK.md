# Project Handbook

## 1. Working principles

This repository is an engineering portfolio and hands-on learning environment. Favor explicit trade-offs, incremental delivery, reproducibility, security by default, and evidence through tests and documentation.

### Definition of Ready

A feature can start when:
- Its problem and user/business outcome are clear.
- Scope and non-goals are explicit.
- Dependencies and important design questions are identified.
- Acceptance criteria are testable.
- Security, data, and operational implications have been considered.

### Definition of Done

A feature is done when:
- Acceptance criteria are met.
- Automated tests cover normal, invalid, and failure paths as appropriate.
- Static analysis, formatting, and relevant quality checks pass.
- API contracts and examples are updated.
- Security and observability implications are addressed.
- Documentation and ADRs are updated when decisions change.
- Docker/local setup remains reproducible.
- A pull request is reviewed and merged.

## 2. Git workflow

- main must remain releasable.
- Create one short-lived branch per issue: feature/123-api-catalog, fix/145-token-validation, docs/151-threat-model.
- Use Conventional Commit messages: feat(api-catalog): add API registration, fix(gateway): reject expired tokens, docs(architecture): describe gateway boundary.
- Open a PR for every change; link the issue with Closes #123.
- Prefer squash merge to keep the history readable.
- Never commit secrets, private keys, production data, or real personal information.
- See the [Contributing Guide](../CONTRIBUTING.md) and [Development Workflow](development/workflow.md) for the full process.

## 3. Issue and feature lifecycle

Each phase is represented by a feature/epic issue. Implementation tasks are separate, small issues with:
- Context and outcome
- Scope and non-goals
- Acceptance criteria
- Technical notes
- Test strategy
- Documentation requirements
- Dependencies

Use labels consistently: type:feature, type:task, type:bug, area:backend, area:frontend, area:infra, area:security, area:docs, priority:high, priority:medium, priority:low. If labels are not available in the repository, include the intended label names in issue descriptions until they can be configured. Do not assume labels, milestones, or a project board exist unless they are visible in GitHub.

## 4. Branch and pull request expectations

A PR should:
- Explain what changed and why.
- Link one or more issues.
- Include test commands and results.
- Include screenshots for user-facing changes.
- Describe API contract or configuration changes.
- State migration/rollback considerations.
- Confirm no secrets or sensitive data are included.

## 5. Quality gates

Backend (to be introduced in Phase 1):
- PHPUnit tests
- PHP-CS-Fixer or equivalent formatting
- PHPStan
- Symfony validation and security tests
- OpenAPI contract validation

Frontend:
- ESLint
- TypeScript typecheck
- Component/unit tests
- Production build

Platform:
- Docker Compose config validation
- Container health checks
- Integration tests for gateway/auth/messaging
- Dependency and secret scanning in CI

Exact commands become authoritative when the corresponding runtime/tooling is implemented. Do not report a quality gate as passed unless it has actually run.

## 6. Security rules

- Store local secrets in ignored .env.local files or secret stores; commit only safe .env.example placeholders.
- Use least privilege for service accounts and database users.
- Validate issuer, audience, signature, expiry, and allowed algorithms for JWTs.
- Never log bearer tokens, passwords, API keys, or sensitive payload fields.
- Apply input validation, payload size limits, timeouts, and rate limits.
- Treat external webhook and connector payloads as untrusted.
- Define retention and redaction rules for integration execution logs.
- Use synthetic data only; never commit production exports or real customer data.

See [Local Setup and Secret Handling](development/local-setup.md).

## 7. Documentation expectations

Every architectural capability should document:
- Purpose and responsibility boundaries
- Request/event flow
- Configuration
- Failure modes and recovery
- Security considerations
- Observability signals
- Local verification steps
- Decisions and alternatives

## 8. Learning and interview preparation

For each major feature, keep a short explanation of:
1. The problem.
2. The chosen design.
3. Alternatives considered.
4. Trade-offs and failure modes.
5. How the implementation is tested and operated.

Do not claim production experience from this portfolio project. Present it as a hands-on architecture and implementation project.

## 9. Local prerequisites

Docker Engine/Desktop with Compose v2 and Git are the baseline prerequisites. PHP 8.4/Composer 2 and Node.js/npm are optional host tools when not using containers. Exact versions and executable commands will be pinned in the runtime foundation phase.

See [Local Development Prerequisites](development/local-setup.md).
