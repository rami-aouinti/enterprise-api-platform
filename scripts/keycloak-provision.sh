#!/usr/bin/env bash
set -euo pipefail

# Configure le client machine, son rôle api.read et les claims de rôles OIDC.
docker compose exec -T keycloak /bin/sh -s <<'REMOTE'
set -eu

KC=/opt/keycloak/bin/kcadm.sh
REALM=enterprise-platform

"$KC" config credentials \
  --server http://localhost:8080 \
  --realm master \
  --user "$KC_BOOTSTRAP_ADMIN_USERNAME" \
  --password "$KC_BOOTSTRAP_ADMIN_PASSWORD"

CLIENT_ID=$("$KC" get clients -r "$REALM" -q clientId=enterprise-api-machine \
  | sed -n 's/.*"id"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' \
  | head -n 1)
test -n "$CLIENT_ID"

"$KC" update "clients/$CLIENT_ID" -r "$REALM" \
  -s "secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"

SERVICE_ACCOUNT_USERNAME=$("$KC" get "clients/$CLIENT_ID/service-account-user" -r "$REALM" \
  | sed -n 's/.*"username"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' \
  | head -n 1)
test -n "$SERVICE_ACCOUNT_USERNAME"

"$KC" add-roles -r "$REALM" \
  --uusername "$SERVICE_ACCOUNT_USERNAME" \
  --rolename api.read

# Register the realm-role mapper directly on the machine client.
# Client-level mappers are applied to this client's tokens without relying
# on optional/default client scopes or their identifiers.
MAPPERS=$("$KC" get "clients/$CLIENT_ID/protocol-mappers/models" -r "$REALM")
if ! printf '%s' "$MAPPERS" | grep -q '"name" : "enterprise-api-realm-roles"'; then
  "$KC" create "clients/$CLIENT_ID/protocol-mappers/models" -r "$REALM" \
    -s name=enterprise-api-realm-roles \
    -s protocol=openid-connect \
    -s protocolMapper=oidc-usermodel-realm-role-mapper \
    -s 'config."claim.name"=realm_access.roles' \
    -s 'config."jsonType.label"=String' \
    -s 'config."multivalued"=true' \
    -s 'config."access.token.claim"=true' \
    -s 'config."id.token.claim"=false' \
    -s 'config."userinfo.token.claim"=true'
fi


# Create the two optional OAuth2 client scopes and grant them to the trusted
# machine client. A client must explicitly request a scope when obtaining a token.
find_client_scope_id() {
  SCOPE_NAME="$1"
  "$KC" get client-scopes -r "$REALM" \
    | awk -v name="$SCOPE_NAME" 'BEGIN { RS="{" } index($0, "\"name\" : \"" name "\"") { if (match($0, /"id" : "[^"]+"/)) { value=substr($0, RSTART, RLENGTH); sub(/^[^"]*"id" : "/, "", value); gsub(/"/, "", value); print value; exit } }'
}

ensure_optional_scope() {
  SCOPE_NAME="$1"
  SCOPE_ID=$(find_client_scope_id "$SCOPE_NAME")

  if [ -z "$SCOPE_ID" ]; then
    echo "Creating missing Keycloak client scope: $SCOPE_NAME"
    "$KC" create client-scopes -r "$REALM" \
      -s "name=$SCOPE_NAME" \
      -s protocol=openid-connect \
      -s 'attributes."include.in.token.scope"=true' \
      -s 'attributes."display.on.consent.screen"=false'
    SCOPE_ID=$(find_client_scope_id "$SCOPE_NAME")
  fi
  test -n "$SCOPE_ID"

  ASSIGNED_SCOPES=$("$KC" get "clients/$CLIENT_ID/optional-client-scopes" -r "$REALM")
  if ! printf '%s' "$ASSIGNED_SCOPES" | grep -q "$SCOPE_ID"; then
    echo "Assigning optional scope $SCOPE_NAME to enterprise-api-machine"
    "$KC" update "clients/$CLIENT_ID/optional-client-scopes/$SCOPE_ID" -r "$REALM"
  fi
}

ensure_optional_scope customers:read
ensure_optional_scope customers:write

# Provision a second machine client with a valid audience but no api.read role.
# Reuse the local-only secret so the negative authorization test needs no extra setup.
NO_READ_CLIENT_ID=$("$KC" get clients -r "$REALM" -q clientId=enterprise-api-no-read \
  | sed -n 's/.*"id"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' \
  | head -n 1)

if [ -z "$NO_READ_CLIENT_ID" ]; then
  "$KC" create clients -r "$REALM" \
    -s clientId=enterprise-api-no-read \
    -s name="Enterprise API No-Read Test Client" \
    -s enabled=true \
    -s protocol=openid-connect \
    -s publicClient=false \
    -s serviceAccountsEnabled=true \
    -s standardFlowEnabled=false \
    -s directAccessGrantsEnabled=false \
    -s "secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"
  NO_READ_CLIENT_ID=$("$KC" get clients -r "$REALM" -q clientId=enterprise-api-no-read \
    | sed -n 's/.*"id"[[:space:]]*:[[:space:]]*"\([^"]*\)".*/\1/p' \
    | head -n 1)
fi
test -n "$NO_READ_CLIENT_ID"

"$KC" update "clients/$NO_READ_CLIENT_ID" -r "$REALM" \
  -s "secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"

NO_READ_MAPPERS=$("$KC" get "clients/$NO_READ_CLIENT_ID/protocol-mappers/models" -r "$REALM")
if ! printf '%s' "$NO_READ_MAPPERS" | grep -q '"name" : "enterprise-api-audience"'; then
  "$KC" create "clients/$NO_READ_CLIENT_ID/protocol-mappers/models" -r "$REALM" \
    -s name=enterprise-api-audience \
    -s protocol=openid-connect \
    -s protocolMapper=oidc-audience-mapper \
    -s 'config."included.client.audience"=enterprise-api' \
    -s 'config."id.token.claim"=false' \
    -s 'config."access.token.claim"=true'
fi

echo "Machine client provisioned with api.read role, realm roles mapper and optional customers:read/customers:write scopes."
echo "Negative-test client enterprise-api-no-read provisioned without api.read and with the enterprise-api audience."
REMOTE
