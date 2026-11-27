# Development Workflow

## Issue lifecycle

1. Select work from the earliest unfinished roadmap phase.
2. Confirm the task's scope, non-goals, dependencies, and acceptance criteria.
3. For a new capability, link the task to its phase-level feature issue.
4. Keep implementation changes focused; create a follow-up issue for unrelated work.
5. Update the issue when assumptions or scope change.

## Main-branch workflow

This repository is intentionally maintained directly on `main`:

- Do not create feature branches.
- Do not open pull requests.
- Work on one issue at a time and keep each change focused.
- Before editing, inspect the current `main` state.
- Commit coherent changes with a Conventional Commit message and the issue number.
- Validate the change and document the exact commands and results.
- Never commit secrets, generated local state, or production data.

Examples:

- `feat(customer-api): add customer read endpoint (#16)`
- `fix(gateway): reject oversized request bodies (#19)`
- `docs(api-governance): document compatibility policy (#21)`
- `test(consumer): verify duplicate event handling (#23)`
- `chore(ci): pin workflow action versions (#35)`

## Definition of Ready

- Business/technical problem is understood.
- Scope and non-goals are written down.
- Dependencies and risks are known.
- Acceptance criteria can be verified.
- Security, data ownership, and operations have been considered.

## Definition of Done

- Acceptance criteria are satisfied.
- Relevant unit, functional, integration, and contract tests pass.
- Formatting and static analysis pass.
- Documentation and examples are updated.
- ADRs are created/updated for material architectural decisions.
- No credentials or sensitive data are present.
- Actual validation results and known limitations are recorded.
- The commit is present on `main`.

## Issue labels

Intended label vocabulary is documented in the Project Handbook. If labels are not yet configured in GitHub, keep the phase/task relationship explicit in issue titles and bodies. Do not assume a label, milestone, or project board exists unless it is visible in the repository.
