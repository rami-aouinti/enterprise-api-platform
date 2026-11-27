# Contributing

Thank you for contributing to Enterprise API Integration Platform. This repository is a hands-on engineering portfolio; changes should be reproducible, tested, secure, and documented.

## Before starting

1. Read the [Project Handbook](docs/PROJECT-HANDBOOK.md).
2. Review the [Roadmap](docs/roadmap/README.md) and the target GitHub issue.
3. Confirm the issue has a clear outcome, scope, dependencies, and testable acceptance criteria.
4. Ask for clarification in the issue rather than silently expanding scope.

## Main-branch workflow

This project uses a direct-to-main workflow by agreement:

- Work directly on `main`.
- Do not create branches or pull requests.
- Work on one ticket at a time and keep commits focused.
- Use Conventional Commits and include the ticket number, for example `feat(api): add customer endpoint (#16)`.
- Inspect the latest main state before editing and avoid overwriting unrelated changes.

## Commits

Use Conventional Commits:

- `feat(api): add customer endpoint (#16)`
- `fix(auth): reject tokens with wrong audience (#18)`
- `docs(architecture): clarify service ownership (#17)`
- `test(integration): cover retry behavior (#23)`
- `chore(ci): pin workflow action versions (#35)`

Keep commits small and explain the reason, not just the file changes.

## Definition of Done

A change is complete when:
- Issue acceptance criteria are met or explicitly deferred with agreement.
- Tests cover relevant success, validation, authorization, and failure paths.
- Formatting, static analysis, and applicable quality checks pass.
- API contracts and examples match implementation.
- Security, privacy, and observability implications are addressed.
- Documentation and ADRs are updated.
- Local setup remains reproducible.
- The change is committed to main with actual validation results recorded.

## Security

Never commit real credentials, private keys, access tokens, customer data, or production exports. Use ignored local environment files and safe placeholders in example files. Report suspected vulnerabilities privately to the repository owner rather than publishing exploit details in an issue.
