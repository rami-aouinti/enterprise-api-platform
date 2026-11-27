# Local Development Setup

## Requirements

- Git 2.4x or newer.
- Docker Engine or Docker Desktop.
- Docker Compose v2.
- A terminal capable of running shell commands.

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

Set a private local `POSTGRES_PASSWORD` and a long random `APP_SECRET` in `.env`. Do not commit this file.

Validate and start:

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

The stack contains Symfony 7.4 / PHP 8.4 and PostgreSQL 17. PostgreSQL data is stored in the persistent `postgres_data` volume. The API connects to the database through the private Compose network using host `database` and port `5432`.

## Health checks

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
bash scripts/smoke-test.sh
docker compose ps
~~~

- `/health` confirms that the Symfony HTTP application responds.
- `/health/ready` executes a lightweight database query and returns HTTP 503 if PostgreSQL is unavailable.
- Compose waits for PostgreSQL's `pg_isready` health check before starting the API.

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

The first dependency resolution should produce a `composer.lock`. Commit that lock file so future local builds and CI resolve identical package versions.

## Logs and lifecycle

~~~bash
docker compose logs -f
docker compose logs -f api
docker compose logs -f database

# Stop containers but preserve database data
docker compose down

# Restart
docker compose up -d
~~~

## Reset local data

**Warning:** this permanently deletes the PostgreSQL volume and all data stored in it.

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

- If Compose reports that `POSTGRES_PASSWORD` is missing, copy `.env.example` to `.env` and set a local value.
- If the API container fails to build, inspect `docker compose build --no-cache api` output.
- If the API remains unhealthy, inspect `docker compose logs api database`.
- If port 8000 or 5432 is already in use, stop the conflicting local service.
- To recreate containers without deleting data, run `docker compose down` followed by `docker compose up -d --build`.
