#!/usr/bin/env sh
set -eu

if [ ! -f .env ]; then
  echo "Missing .env. Copy .env.example and set the local credentials first." >&2
  exit 1
fi

# .env values used by this script must be simple shell-safe values.
set -a
. ./.env
set +a

: "${KEYCLOAK_MACHINE_CLIENT_SECRET:?Set KEYCLOAK_MACHINE_CLIENT_SECRET in .env}"

DISCOVERY_URL="http://localhost:8081/realms/enterprise-platform/.well-known/openid-configuration"
TOKEN_URL="http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token"

curl --fail --silent --show-error "$DISCOVERY_URL" | jq -e '
  .issuer == "http://localhost:8081/realms/enterprise-platform"
  and (.jwks_uri | type == "string")
' >/dev/null

TOKEN_RESPONSE=$(curl --fail --silent --show-error \
  -X POST "$TOKEN_URL" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-urlencode "grant_type=client_credentials" \
  --data-urlencode "client_id=enterprise-api-machine" \
  --data-urlencode "client_secret=$KEYCLOAK_MACHINE_CLIENT_SECRET")

ACCESS_TOKEN=$(printf '%s' "$TOKEN_RESPONSE" | jq -er '.access_token')
printf '%s' "$ACCESS_TOKEN" | awk -F. 'NF == 3 { exit 0 } { exit 1 }'

printf '%s' "$ACCESS_TOKEN" | jq -R '
  split(".")[1] | gsub("-"; "+") | gsub("_"; "/")
  | . + (["", "==", "="][length % 4])
  | @base64d | fromjson
  | {iss, aud, azp, exp, iat, realm_access}
'

printf '%s\n' "Client Credentials token flow: OK"
