#!/usr/bin/env bash
set -Eeuo pipefail

API_BASE_URL="${API_BASE_URL:-http://127.0.0.1:8000}"

echo "Checking API liveness..."
curl --fail --silent --show-error "${API_BASE_URL}/health"
echo

echo "Checking API readiness and PostgreSQL connectivity..."
curl --fail --silent --show-error "${API_BASE_URL}/health/ready"
echo

echo "Checking unknown routes return 404..."
status="$(curl --silent --output /dev/null --write-out '%{http_code}' "${API_BASE_URL}/not-found")"
if [[ "${status}" != "404" ]]; then
    echo "Expected HTTP 404, received HTTP ${status}" >&2
    exit 1
fi

echo "Smoke tests passed."
