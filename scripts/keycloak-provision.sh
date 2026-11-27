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

# The persistent Keycloak volume may predate the current realm import.
# Explicitly attach the built-in "roles" client scope so realm_access.roles
# is emitted in access tokens even when the realm was not freshly imported.
ROLES_SCOPE_ID=$("$KC" get client-scopes -r "$REALM" -q name=roles \
  | sed -n 's/.*"id"[[:space:]]*:[[:space:]]*"\\([^"]*\\)".*/\\1/p' \
  | head -n 1)
test -n "$ROLES_SCOPE_ID"

"$KC" update "clients/$CLIENT_ID/default-client-scopes/$ROLES_SCOPE_ID" -r "$REALM"

echo "Machine client provisioned with api.read role and OIDC roles scope."
REMOTE
