# Local Development Prerequisites and Setup Policy

The repository is currently in its documentation/foundation phase. This page records prerequisites and safe setup conventions; exact service commands will be added with the Docker Compose and Symfony implementation tickets.

## Required tools
- Git 2.4x or newer.
- Docker Engine or Docker Desktop with Docker Compose v2.
- A terminal capable of running shell commands.
- A GitHub account with access to this repository.

## Recommended tools
- Visual Studio Code or another IDE with PHP, Symfony, TypeScript, YAML, and Docker support.
- PHP 8.4 and Composer 2 when running backend tooling directly on the host.
- Node.js LTS and npm when running frontend tooling directly on the host.
- Make, if a Makefile is introduced for common commands.

Docker is the default execution environment to reduce differences between host operating systems. Host-installed PHP/Node versions are optional when containers provide the required runtimes.

## Verify prerequisites
Run the following commands and confirm that each tool reports a version:

~~~bash
git --version
docker --version
docker compose version
~~~

If developing outside containers, also verify:

~~~bash
php --version
composer --version
node --version
npm --version
~~~

The exact required versions will be pinned in the implementation and CI configuration. Do not infer that the application is runnable before the runtime foundation phase is complete.

## Environment files and secrets
- Commit safe examples such as .env.example with placeholder values only.
- Keep real local values in ignored files such as .env.local or an untracked local secrets file.
- Never commit passwords, tokens, private keys, certificates, production connection strings, or personal/customer data.
- Prefer environment injection or a secrets manager over embedding credentials in Docker Compose files.
- Rotate any credential that is accidentally exposed; removing it from the latest commit is not sufficient.
- Do not paste secrets into GitHub issues, pull requests, logs, screenshots, or chat transcripts.

## Local data
Use synthetic development data only. Do not import production or real customer records. Document reset and migration behavior when database services are introduced.

## Next implementation
Ticket #14 introduces Docker Compose and will replace this planning guidance with exact startup, health-check, reset, and troubleshooting commands.
