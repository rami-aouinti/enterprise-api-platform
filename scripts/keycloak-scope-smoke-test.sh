#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f .env ]]; then
  echo "Missing .env. Copy .env.example and set the local credentials first." >&2
  exit 1
fi

set -a
source ./.env
set +a

: "${KEYCLOAK_MACHINE_CLIENT_SECRET:?Set KEYCLOAK_MACHINE_CLIENT_SECRET in .env}"

TOKEN_URL="http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token"
GATEWAY_URL="${GATEWAY_URL:-http://localhost:8001}"
CLIENT_ID="enterprise-api-machine"
RESPONSE_BODY=$(mktemp)
trap 'rm -f "$RESPONSE_BODY"' EXIT

get_token() {
  local requested_scope="${1:-}"
  local args=(
    --fail --silent --show-error
    -X POST "$TOKEN_URL"
    -H "Content-Type: application/x-www-form-urlencoded"
    --data-urlencode "grant_type=client_credentials"
    --data-urlencode "client_id=$CLIENT_ID"
    --data-urlencode "client_secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"
  )

  if [[ -n "$requested_scope" ]]; then
    args+=(--data-urlencode "scope=$requested_scope")
  fi

  curl "${args[@]}" | jq -er '.access_token'
}

request_status() {
  local method="$1"
  local path="$2"
  local token="${3:-}"
  local body="${4:-}"
  local args=(-sS -o "$RESPONSE_BODY" -w '%{http_code}' -X "$method")

  if [[ -n "$token" ]]; then
    args+=(-H "Authorization: Bearer $token")
  fi

  if [[ -n "$body" ]]; then
    args+=(-H "Content-Type: application/json" --data "$body")
  fi

  curl "${args[@]}" "$GATEWAY_URL$path"
}

expect_status() {
  local label="$1"
  local expected="$2"
  shift 2

  local actual
  actual=$(request_status "$@")

  if [[ "$actual" != "$expected" ]]; then
    echo "FAIL: $label (expected HTTP $expected, got HTTP $actual)" >&2
    cat "$RESPONSE_BODY" >&2
    exit 1
  fi

  echo "PASS: $label -> HTTP $actual"
}

echo "Requesting Keycloak client-credentials tokens..."
TOKEN_NO_SCOPE=$(get_token)
TOKEN_READ=$(get_token "customers:read")
TOKEN_WRITE=$(get_token "customers:write")
TOKEN_APIS_READ=$(get_token "apis:read")
TOKEN_APIS_WRITE=$(get_token "apis:write")

tamper_token_signature() {
  local token="$1"
  local header payload signature replacement
  IFS='.' read -r header payload signature <<< "$token"

  if [[ -z "$header" || -z "$payload" || -z "$signature" ]]; then
    echo "Cannot tamper with a malformed JWT." >&2
    return 1
  fi

  replacement="A"
  if [[ "${signature:0:1}" == "$replacement" ]]; then
    replacement="B"
  fi

  printf '%s.%s.%s' "$header" "$payload" "$replacement${signature:1}"
}

TOKEN_BAD_SIGNATURE=$(tamper_token_signature "$TOKEN_NO_SCOPE")

echo
echo "Checking JWT authentication and scope-based authorization..."

expect_status "Health endpoint remains public" 200 GET "/health"
expect_status "Missing token is rejected" 401 GET "/api/v1/customers"
expect_status "Malformed JWT is rejected" 401 GET "/api/v1/customers" "not-a-jwt"
expect_status "JWT with an invalid signature is rejected" 401 GET "/api/v1/customers" "$TOKEN_BAD_SIGNATURE"
expect_status "Token without customers:read cannot list customers" 403 GET "/api/v1/customers" "$TOKEN_NO_SCOPE"
expect_status "customers:read can list customers" 200 GET "/api/v1/customers" "$TOKEN_READ"

expect_status "Read-only token cannot create customers" 403 POST "/api/v1/customers" "$TOKEN_READ" '{}'
expect_status "customers:write reaches create validation" 422 POST "/api/v1/customers" "$TOKEN_WRITE" '{}'

CUSTOMER_ID="123e4567-e89b-42d3-a456-426614174000"
expect_status "Read-only token cannot update customers" 403 PATCH "/api/v1/customers/$CUSTOMER_ID" "$TOKEN_READ" '{"status":"inactive"}'
expect_status "customers:write reaches update lookup" 404 PATCH "/api/v1/customers/$CUSTOMER_ID" "$TOKEN_WRITE" '{"status":"inactive"}'

expect_status "Token without apis:read cannot list the API registry" 403 GET "/api/v1/registry/apis" "$TOKEN_NO_SCOPE"
expect_status "apis:read can list the API registry" 200 GET "/api/v1/registry/apis" "$TOKEN_APIS_READ"
expect_status "Read-only API registry token cannot register an API" 403 POST "/api/v1/registry/apis" "$TOKEN_APIS_READ" '{}'
expect_status "apis:write reaches API registry validation" 422 POST "/api/v1/registry/apis" "$TOKEN_APIS_WRITE" '{}'

echo
echo "Keycloak JWT authentication and OAuth2 scope authorization: OK"
