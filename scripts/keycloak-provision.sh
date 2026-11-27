#!/usr/bin/env bash
set -euo pipefail

# Applique le secret du client machine local et lui attribue le rôle minimal.
docker compose exec -T keycloak /bin/sh -ec '
  KC=/opt/keycloak/bin/kcadm.sh
  REALM=enterprise-platform

  "$KC" config credentials \
    --server http://localhost:8080 \
    --realm master \
    --user "$KC_BOOTSTRAP_ADMIN_USERNAME" \
    --password "$KC_BOOTSTRAP_ADMIN_PASSWORD"

  CLIENT_ID=$("$KC" get clients -r "$REALM" -q clientId=enterprise-api-machine \
    | grep -m 1 '"'"'id'"'"' | cut -d '"' -f 4)
  test -n "$CLIENT_ID"

  "$KC" update "clients/$CLIENT_ID" -r "$REALM" \
    -s "secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"

  SERVICE_ACCOUNT_USERNAME=$("$KC" get "clients/$CLIENT_ID/service-account-user" -r "$REALM" \
    | grep -m 1 '"'"'username'"'"' | cut -d '"' -f 4)
  test -n "$SERVICE_ACCOUNT_USERNAME"

  "$KC" add-roles -r "$REALM" --uusername "$SERVICE_ACCOUNT_USERNAME" --rolename api.read
  echo "Machine client provisioned with api.read role."
'
