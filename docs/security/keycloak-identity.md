# Keycloak identity and token flows

## Local identity provider

Docker Compose starts Keycloak at `http://localhost:8081` and imports the
`enterprise-platform` realm from
`infrastructure/keycloak/realm/enterprise-platform-realm.json`.

The local realm provisions:

| Component | Purpose |
|---|---|
| `enterprise-portal` | Public React/browser client using Authorization Code + PKCE (S256) |
| `enterprise-api` | Resource-server audience identifier |
| `enterprise-api-machine` | Confidential machine-to-machine client using Client Credentials |
| `portal-demo` | Synthetic interactive test user |
| `portal-user`, `api.read`, `api.write`, `platform-admin` | Realm roles |
| `enterprise-api-audience` | Adds `enterprise-api` to access-token audience |

The local-only demo user's password is `local-demo-only`. Never reuse it outside
this disposable development realm.

## Start and provision

Create a private local environment file and replace every placeholder:

~~~bash
cp .env.example .env
~~~

Use shell-safe random values for the three passwords/secrets. For example:

~~~bash
openssl rand -hex 32
~~~

Start the platform and wait for Keycloak:

~~~bash
docker compose config --quiet
docker compose up -d
docker compose ps
curl --fail http://localhost:8081/health/ready
~~~

Apply the machine-client secret from the ignored `.env` file and grant its
service account the least-privilege `api.read` role:

~~~bash
sh scripts/keycloak-provision.sh
~~~

The confidential client secret is supplied to the container at runtime. It is
not stored in the realm export, source code, or browser assets. Retrieve or
rotate it through the local secret configuration and rerun provisioning.

## Authorization Code with PKCE (public browser client)

The portal client is public: it has no client secret. It has Standard Flow
enabled, Direct Access Grants and Implicit Flow disabled, and requires PKCE
with the S256 challenge method. Its local callback allow-list is
`http://localhost:5173/*`.

Generate a verifier and S256 challenge:

~~~bash
CODE_VERIFIER=$(openssl rand -base64 64 | tr -d '=+/\n' | cut -c1-64)
CODE_CHALLENGE=$(printf '%s' "$CODE_VERIFIER" | openssl dgst -sha256 -binary | openssl base64 -A | tr '+/' '-_' | tr -d '=')
echo "$CODE_VERIFIER"
echo "$CODE_CHALLENGE"
~~~

Open this authorization URL in a browser, replacing
`URL_ENCODED_CHALLENGE` with the generated challenge:

~~~text
http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/auth?client_id=enterprise-portal&response_type=code&scope=openid%20profile%20email&redirect_uri=http%3A%2F%2Flocalhost%3A5173%2Fauth%2Fcallback&code_challenge=URL_ENCODED_CHALLENGE&code_challenge_method=S256
~~~

Sign in as `portal-demo` with the local-only password. The browser redirects
to the callback with a short-lived authorization code. Exchange that code from
the client application, using the original verifier:

~~~bash
curl --fail --request POST \
  http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=authorization_code' \
  --data-urlencode 'client_id=enterprise-portal' \
  --data-urlencode 'redirect_uri=http://localhost:5173/auth/callback' \
  --data-urlencode 'code=AUTHORIZATION_CODE_FROM_CALLBACK' \
  --data-urlencode "code_verifier=$CODE_VERIFIER"
~~~

The redirect URI must exactly match the URI used in the authorization request.
The verifier stays in the browser application's transient session storage;
only the S256 challenge is sent through the authorization request. Do not put
a client secret in a React environment variable or compiled asset.

## Client Credentials (machine identity)

After provisioning, run the local smoke test:

~~~bash
sh scripts/keycloak-token-smoke-test.sh
~~~

It obtains an access token for `enterprise-api-machine` without a user session
and prints selected claims (not the token or client secret). The service
account is assigned `api.read`; do not grant broad administrative roles to
machine clients by default.

Equivalent token request:

~~~bash
curl --fail --request POST \
  http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/token \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'grant_type=client_credentials' \
  --data-urlencode 'client_id=enterprise-api-machine' \
  --data-urlencode "client_secret=$KEYCLOAK_MACHINE_CLIENT_SECRET"
~~~

## Discovery, issuer, audience, and JWT validation

Realm discovery document:

- `http://localhost:8081/realms/enterprise-platform/.well-known/openid-configuration`

Expected issuer:

- `http://localhost:8081/realms/enterprise-platform`

JWKS endpoint is published as the discovery document's `jwks_uri`, normally:

- `http://localhost:8081/realms/enterprise-platform/protocol/openid-connect/certs`

Every API/resource server must validate an access token before trusting its
claims:

1. Verify the signature using a trusted public key from the issuer's JWKS.
2. Allow only the configured asymmetric signing algorithm (do not accept
   `none` or choose an algorithm from an untrusted token header).
3. Require an exact issuer match.
4. Require the expected audience `enterprise-api`; a valid signature alone
   is not sufficient.
5. Enforce `exp`, `nbf` when present, and a small bounded clock skew.
6. Apply application authorization using roles/scopes after authentication.
7. Reject ID tokens when an API access token is required.

The realm's access-token lifetime is five minutes. Refresh tokens are rotated
(`revokeRefreshToken=true`, `refreshTokenMaxReuse=0`) for interactive
sessions. Machine clients should request a new access token when needed rather
than retaining long-lived tokens.

The current ticket provisions the identity provider and demonstrates both
grant flows. It does not yet add a Symfony JWT authenticator or protect the
Customer API routes; API/gateway enforcement and authorization are handled in
the later security/gateway work.

## Signing-key rotation and JWKS caching

Keycloak publishes active public signing keys through JWKS. Resource servers
must cache keys for a bounded period, select the key by JWT header `kid`, and
refresh JWKS once when an unknown `kid` is encountered. They must not pin one
public key indefinitely.

For a controlled rotation:

1. Add/generate a new active realm signing key in **Realm settings → Keys**.
2. Keep the previous key available during the overlap period so already-issued
   tokens remain verifiable.
3. Confirm discovery/JWKS exposes the new key and verify a newly issued token
   with its `kid`.
4. Wait at least the maximum access-token lifetime plus clock-skew allowance.
5. Retire the old key only after tokens signed by it can no longer be valid.

Monitor issuer/JWKS availability and never log access tokens, refresh tokens,
authorization codes, or client secrets.

## Local reset and troubleshooting

Realm import happens when the realm is first created. If you intentionally
need to re-import a changed realm definition, back up anything important and
remove only the Keycloak volume:

~~~bash
docker compose down
docker volume rm enterprise-api-platform_keycloak_data
docker compose up -d keycloak
~~~

This deletes local Keycloak users, sessions, and realm data. It does not remove
the PostgreSQL volume.

- Read logs: `docker compose logs -f keycloak`.
- Check readiness: `curl -i http://localhost:8081/health/ready`.
- If a secret changes in `.env`, rerun `sh scripts/keycloak-provision.sh`.
- If the browser receives `invalid_redirect_uri`, compare the callback with
  the exact redirect URI registered on `enterprise-portal`.
