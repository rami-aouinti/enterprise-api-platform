#!/usr/bin/env bash
set -euo pipefail

EXCEPTIONS_FILE="docs/api/compatibility-exceptions.txt"
TODAY=$(date -u +%F)

if [[ ! -f "$EXCEPTIONS_FILE" ]]; then
    echo "Missing compatibility exception register: $EXCEPTIONS_FILE" >&2
    exit 1
fi

active_count=0

while IFS= read -r line || [[ -n "${line}" ]]; do
    [[ -z "${line//[[:space:]]/}" || "$line" =~ ^[[:space:]]*# ]] && continue

    if [[ ! "$line" =~ ^(GET|POST|PUT|PATCH|DELETE|OPTIONS|HEAD)[[:space:]]+[^[:space:]]+[[:space:]]+[^|]+[[:space:]]+\|[[:space:]]*issue=#[0-9]+[[:space:]]+\|[[:space:]]*owner=[^|]+[[:space:]]+\|[[:space:]]*reviewer=[^|]+[[:space:]]+\|[[:space:]]*expires=([0-9]{4}-[0-9]{2}-[0-9]{2})[[:space:]]+\|[[:space:]]*reason=.+$ ]]; then
        echo "Invalid compatibility exception entry: $line" >&2
        echo "Required: METHOD /path change | issue=#123 | owner=team | reviewer=name | expires=YYYY-MM-DD | reason=..." >&2
        exit 1
    fi

    expiry="${BASH_REMATCH[2]}"
    normalized_expiry=$(date -d "$expiry" +%F 2>/dev/null || true)
    if [[ "$normalized_expiry" != "$expiry" || "$expiry" < "$TODAY" ]]; then
        echo "Compatibility exception has an invalid or expired date ($expiry): $line" >&2
        exit 1
    fi

    ((active_count += 1))
done < "$EXCEPTIONS_FILE"

echo "Compatibility exception register valid ($active_count active exceptions)."
