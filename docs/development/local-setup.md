# Local Development Setup

## Requirements

- Git 2.4x or newer.
- Docker Engine or Docker Desktop.
- Docker Compose v2.
- A terminal capable of running shell commands.

Host-installed PHP, Composer, Node.js, and npm are optional. The project uses containers to keep the runtime consistent across developer machines.

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

Open `.env` and replace `POSTGRES_PASSWORD` with a private local password. Do not commit this file.

Validate and start the stack:

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

The stack contains:

| Service | Container | Local endpoint | Purpose |
|---|---|---|---|
| API | enterprise-api | http://localhost:8000 | PHP API bootstrap |
| PostgreSQL | enterprise-api-database | localhost:5432 | Persistent relational database |

The API connects to PostgreSQL over the private Compose network using host `database` and port `5432`. Host ports are bound to loopback only.

## Health checks

~~~bash
curl -i http://localhost:8000/health
curl -i http://localhost:8000/health/ready
docker compose ps
~~~

- `/health` confirms that the HTTP API process responds.
- `/health/ready` confirms that the API can connect to PostgreSQL.
- Compose waits for PostgreSQL's `pg_isready` health check before starting the API.

Both HTTP endpoints should return status 200 when the stack is ready.

## Logs and lifecycle

~~~bash
# All logs
docker compose logs -f

# API logs only
docker compose logs -f api

# Database logs only
docker compose logs -f database

# Stop containers; preserve database data
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
- If port 8000 or 5432 is already in use, stop the conflicting local service before starting Compose.
- If the API remains unhealthy, inspect `docker compose logs api database`.
- To recreate containers without deleting data, run `docker compose down` followed by `docker compose up -d --build`.

The current API is a minimal PHP bootstrap for the infrastructure ticket. The Symfony application is introduced in the next backend implementation ticket.
