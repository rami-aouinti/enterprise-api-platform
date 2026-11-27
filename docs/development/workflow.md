# Development and Delivery Workflow

## Issue lifecycle
1. Select work from the earliest unfinished roadmap phase.
2. Confirm the task's scope, non-goals, dependencies, and acceptance criteria.
3. For a new capability, link the task to its phase-level feature issue.
4. Keep implementation changes focused; create a follow-up issue for unrelated work.
5. Update the issue when assumptions or scope change.

## Branch naming
Use one short-lived branch per task:
- feature/123-customer-api
- fix/145-jwt-audience-validation
- docs/151-architecture-guide
- chore/160-ci-dependency-updates

Use the issue number in the branch name. Do not reuse long-lived feature branches.

## Commit convention
Follow Conventional Commits with a meaningful scope:
- feat(customer-api): add customer read endpoint
- fix(gateway): reject oversized request bodies
- docs(api-governance): document compatibility policy
- test(consumer): verify duplicate event handling
- chore(deps): update pinned tool version

A commit should represent one coherent change and should not contain secrets or generated local state.

## Pull request process
1. Push the focused branch.
2. Open a PR targeting main.
3. Include the problem, solution, design trade-offs, and affected components.
4. Link the issue using Closes #123.
5. List exact validation commands and their outcomes.
6. Explain config, API contract, schema, security, and operational changes.
7. Request review; resolve feedback and keep the PR focused.
8. Squash merge after required checks and review pass.

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
- CI is green and PR review is complete.
- Demo or reproducible verification steps are available.

## Issue labels
Intended label vocabulary is documented in the Project Handbook. If labels are not yet configured in GitHub, keep the phase/task relationship explicit in issue titles and bodies. Do not assume a label, milestone, or project board exists unless it is visible in the repository.
