# API governance workflow

The repository uses two complementary tools:

- Spectral validates the style and structural conventions of OpenAPI contracts.
- oasdiff validates each contract and compares changed contracts with their previous Git version to detect breaking changes.

The ruleset lives in .spectral.yaml. Compatibility policy and approved exceptions live under docs/api. Both tools are pinned in CI so local and automated results are reproducible.

Run the checks for changed contracts with:

~~~bash
bash scripts/check-openapi-contracts.sh origin/main
~~~

For a local commit, pass its parent SHA or another base ref. A deleted contract is treated as breaking. Newly added contracts are linted, but have no previous version to compare.

A breaking change requires a major semantic-version increase. An exception must be added to docs/api/compatibility-exceptions.txt with an issue, owner, reviewer, expiry and reason. Expired or malformed exceptions fail validation.
