# Local Development Setup

## Requirements

- Git 2.4x or newer.
- Docker Engine or Docker Desktop.
- Docker Compose v2.
- A terminal capable of running shell commands.
- `curl`, `jq`, and `openssl` for identity-flow smoke tests.

Host-installed PHP and Composer are optional; the API container provides PHP 8.4 and Composer.

Verify the required tools:

~~~bash
git --version
docker --version
docker compose version
~~~

## First startup

From the repository root:

~~~bash
cp .env.example .env
~~~

Set private local values for `POSTGRES_PASSWORD`, `APP_SECRET`,
`KEYCLOAK_ADMIN_PASSWORD`, and `KEYCLOAK_MACHINE_CLIENT_SECRET` in `.env`.
Use long random values and do not commit this file.

Validate and start:

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

The stack contains Symfony 7.4 / PHP 8.4, PostgreSQL 17, and Keycloak 26.3.3.
PostgreSQL and Keycloak data are stored in separate persistent volumes. The API
connects to PostgreSQL through the private Compose network. Keycloak is
available at `http://localhost:8081`; its local admin credentials come from
the ignored `.env` file.

Provision the confidential machine client after Keycloak is ready:

~~~bash
curl --fail http://localhost:8081/health/ready
sh scripts/keycloak-provision.sh
~~~

See [Keycloak identity flows](../security/keycloak-identity.md) for the PKCE
browser flow, Client Credentials flow, JWT claims, JWKS validation, and key
rotation procedure.

## Health checks

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
curl --fail http://localhost:8081/health/ready
bash scripts/smoke-test.sh
sh scripts/keycloak-token-smoke-test.sh
docker compose ps
~~~

- `/health` confirms that the Symfony HTTP application responds.
- `/health/ready` executes a lightweight database query and returns HTTP 503 if PostgreSQL is unavailable.
- Keycloak's `/health/ready` endpoint confirms that the identity provider is ready.
- The token smoke test validates discovery and obtains a machine access token.

## Backend commands

~~~bash
# Symfony environment and configuration overview
docker compose exec api php bin/console about

# Tests
docker compose exec api composer test
docker compose exec api composer test:unit
docker compose exec api composer test:functional

# Static analysis and formatting
docker compose exec api composer analyse
docker compose exec api composer cs:check
~~~

The first dependency resolution creates `composer.lock` inside the container. Copy and commit it to make builds and CI reproducible:

~~~bash
docker compose cp api:/app/composer.lock ./composer.lock
git add composer.lock
git commit -m "chore(deps): lock Symfony backend dependencies (#15)"
git push origin main
~~~

## Logs and lifecycle

~~~bash
docker compose logs -f
docker compose logs -f api
docker compose logs -f database
docker compose logs -f keycloak

# Stop containers but preserve database and identity data
docker compose down

# Restart
docker compose up -d
~~~

## Reset local data

**Warning:** this permanently deletes the PostgreSQL and Keycloak volumes and all data stored in them.

~~~bash
docker compose down -v
docker compose up -d --build
~~~

## Environment files and secrets

- Commit safe examples such as `.env.example` with placeholder values only.
- Keep real local values in ignored `.env` or `.env.local` files.
- Never commit passwords, tokens, private keys, certificates, production connection strings, or personal/customer data.
- Prefer environment injection or a secrets manager over embedding credentials in Docker Compose files.
- Rotate any credential that is accidentally exposed; removing it from the latest commit is not sufficient.
- Use synthetic development data only. Never import production or real customer records.

## Troubleshooting

- If Compose reports a missing variable, copy `.env.example` to `.env` and set all required local values.
- If the API container fails to build, inspect `docker compose build --no-cache api` output.
- If the API remains unhealthy, inspect `docker compose logs api database`.
- If Keycloak does not become ready, inspect `docker compose logs keycloak` and verify ports 8081 and 9000 are available.
- If port 8000, 5432, or 8081 is already in use, stop the conflicting local service.
- To recreate containers without deleting data, run `docker compose down` followed by `docker compose up -d --build`.
