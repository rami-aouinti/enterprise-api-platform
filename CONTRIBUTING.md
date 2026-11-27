# Contributing

Thank you for contributing to Enterprise API Integration Platform. This repository is a hands-on engineering portfolio; changes should be reproducible, tested, secure, and documented.

## Before starting
1. Read the [Project Handbook](docs/PROJECT-HANDBOOK.md).
2. Review the [Roadmap](docs/roadmap/README.md) and the target GitHub issue.
3. Confirm the issue has a clear outcome, scope, dependencies, and testable acceptance criteria.
4. Ask for clarification in the issue rather than silently expanding scope.

## Branch workflow
- Keep main releasable.
- Create one short-lived branch per issue.
- Naming: feature/123-short-description, fix/123-short-description, docs/123-short-description, or chore/123-short-description.
- Do not commit implementation directly to main.
- Keep a branch focused on one issue and rebase/update it when main changes.

## Commits
Use Conventional Commits:
- feat(api): add customer endpoint
- fix(auth): reject tokens with wrong audience
- docs(architecture): clarify service ownership
- test(integration): cover retry behavior
- chore(ci): pin workflow action versions

Keep commits small and explain the reason, not just the file changes.

## Pull requests
- Use the repository pull request template.
- Reference the issue with Closes #123 (or Fixes #123 / Resolves #123).
- Explain behavior and design choices.
- Include test commands and actual results; do not claim checks that were not run.
- Document API, schema, environment, and operational changes.
- Include screenshots for UI changes.
- State risks, migration/rollback notes, and known limitations.
- Confirm that no secrets, personal data, or production data are included.

## Definition of Done
A change is complete when:
- Issue acceptance criteria are met or explicitly deferred with agreement.
- Tests cover relevant success, validation, authorization, and failure paths.
- Formatting, static analysis, and applicable CI checks pass.
- API contracts and examples match implementation.
- Security, privacy, and observability implications are addressed.
- Documentation and ADRs are updated.
- Local setup remains reproducible.
- The PR is reviewed and merged.

## Security
Never commit real credentials, private keys, access tokens, customer data, or production exports. Use ignored local environment files and safe placeholders in example files. Report suspected vulnerabilities privately to the repository owner rather than publishing exploit details in an issue.
