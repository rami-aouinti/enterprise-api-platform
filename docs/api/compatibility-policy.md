# API contract governance and compatibility policy

## Continuous checks

Every changed OpenAPI contract under docs/api is linted with the repository Spectral ruleset and validated with oasdiff. The rules enforce:

- API contact information and descriptions.
- Unique lower-camelCase operation IDs.
- Defined tags and a success response for every operation.
- OAuth2 security on every operation, with documented 401 and 403 responses.
- Valid path parameters, kebab-case path segments and semantic contract versions.

The CI compatibility check compares each changed contract with its version at the base Git commit. Every detected breaking change fails CI by default with a GitHub annotation. An intentional breaking change must be released under a new major info.version and receive a narrow, reviewed exception in the register. A major bump alone does not bypass the check. Deleting a contract is always rejected: deprecate it first and plan its removal through a major-version migration.

## Breaking-change exceptions

Exceptions are exceptional, narrow and temporary. Do not disable a whole check or silence an entire API.

An approved exception must be a single line in compatibility-exceptions.txt using this format:

~~~text
METHOD /path concise-breaking-change | issue=#123 | owner=team | reviewer=reviewer | expires=YYYY-MM-DD | reason=business justification
~~~

Requirements:

1. Link a GitHub issue describing the business reason and consumer impact.
2. Identify the accountable API owner.
3. Identify the reviewer who approved the exception.
4. Provide a migration plan for affected consumers.
5. Set an expiry date. Expired entries fail CI and must be removed or explicitly re-approved.
6. Keep the exception specific to one operation and one detected breaking change.

The exception register is consumed by oasdiff. Its metadata and expiry are validated by scripts/validate-openapi-exceptions.sh. A code review must approve every change to this file.

## Local commands

~~~bash
npm install --global @stoplight/spectral-cli@6.17.1
go install github.com/oasdiff/oasdiff@v1.41.0
spectral lint docs/api/customer-service.openapi.yaml --ruleset .spectral.yaml --fail-severity error
spectral lint docs/api/api-registry.openapi.yaml --ruleset .spectral.yaml --fail-severity error
oasdiff validate docs/api/customer-service.openapi.yaml
bash scripts/validate-openapi-exceptions.sh
~~~

To compare a local contract with its version on main:

~~~bash
oasdiff breaking origin/main:docs/api/customer-service.openapi.yaml docs/api/customer-service.openapi.yaml
~~~

In CI, the repository script checks only changed contracts against the base Git revision.
