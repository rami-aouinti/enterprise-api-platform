# Customer Service API

The Customer Service is the first domain API in the platform. It is implemented in the Symfony application and exposed under /api/v1.

## Resource model

A customer contains a UUID, first and last name, unique email address, optional phone number, lifecycle status (active or inactive), and UTC creation/update timestamps. Email addresses are trimmed and normalized to lowercase. The database enforces uniqueness.

## Endpoints

| Method | Path | Purpose | Success |
| --- | --- | --- | --- |
| GET | /api/v1/customers | List customers | 200 |
| POST | /api/v1/customers | Create a customer | 201 |
| GET | /api/v1/customers/{id} | Retrieve a customer | 200 |
| PATCH | /api/v1/customers/{id} | Partially update a customer | 200 |

Collection pagination uses page (one-based) and per_page (1–100, default 20). The response contains data and a meta object with page, page size, total records, and total pages.

## Error behavior

Errors use application/problem+json and the RFC 9457 Problem Details shape. Validation failures return 422 with an errors object. Malformed JSON returns 400, unsupported media types return 415, duplicate email addresses return 409, and missing customers return 404. Internal database details are not returned.

## Database setup

Apply SQL migrations from the API container with:

    docker compose exec api php bin/console app:database:migrate

The migration runner records applied files in schema_migrations and applies each migration transactionally.

## OpenAPI contract

The contract-first API description is maintained at docs/api/customer-service.openapi.yaml (OpenAPI 3.1). Update the contract and implementation together. The automated contract test verifies the required resource operations and core schemas.
