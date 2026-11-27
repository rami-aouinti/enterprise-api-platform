#!/usr/bin/env bash
set -euo pipefail

BASE_REF="${1:?Usage: check-openapi-contracts.sh <base-git-ref>}"

if [[ "$BASE_REF" =~ ^0+$ ]]; then
    echo "No previous commit is available; linting all OpenAPI contracts."
    CHANGED_SPECS=$(find docs/api -maxdepth 1 -type f -name '*.openapi.yaml' -print | sort)
    RUN_BREAKING_CHECK=false
else
    if ! git cat-file -e "$BASE_REF^{commit}" 2>/dev/null; then
        git fetch --no-tags origin "$BASE_REF" --depth=1
    fi

    DELETED_SPECS=$(git diff --no-renames --name-only --diff-filter=D "$BASE_REF"...HEAD -- 'docs/api/*.openapi.yaml')
    if [[ -n "$DELETED_SPECS" ]]; then
        echo "Removing an OpenAPI contract is a breaking change. Deprecate it first and follow the approved major-version migration process:" >&2
        echo "$DELETED_SPECS" >&2
        exit 1
    fi

    CHANGED_SPECS=$(git diff --no-renames --name-only --diff-filter=ACMRT "$BASE_REF"...HEAD -- 'docs/api/*.openapi.yaml')
    RULES_CHANGED=$(git diff --name-only "$BASE_REF"...HEAD -- .spectral.yaml .oasdiff.yaml docs/api/compatibility-exceptions.txt)

    if [[ -n "$RULES_CHANGED" ]]; then
        CHANGED_SPECS=$(find docs/api -maxdepth 1 -type f -name '*.openapi.yaml' -print | sort)
    fi

    RUN_BREAKING_CHECK=true
fi

if [[ -z "$CHANGED_SPECS" ]]; then
    echo "No OpenAPI contract changes detected."
    exit 0
fi

bash scripts/validate-openapi-exceptions.sh

while IFS= read -r spec; do
    [[ -z "$spec" ]] && continue
    echo "Spectral lint: $spec"
    spectral lint "$spec" --ruleset .spectral.yaml --fail-severity error
    oasdiff validate "$spec"
done <<< "$CHANGED_SPECS"

if [[ "$RUN_BREAKING_CHECK" == true ]]; then
    echo "Checking backward compatibility against $BASE_REF"
    mapfile -t SPEC_ARGS <<< "$CHANGED_SPECS"
    oasdiff breaking-files --format githubactions --base "$BASE_REF" "${SPEC_ARGS[@]}"
else
    echo "Breaking-change comparison skipped because this is the initial commit."
fi
