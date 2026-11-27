# Observability and Operations

## Signals

### Metrics
- Request count, error count, and request duration by service/route/status.
- Gateway upstream failures and rate-limit rejections.
- Broker queue depth, consumer lag, retries, and dead-letter counts.
- Integration execution counts, duration, and terminal failure rate.
- Database connection pool and query latency indicators.

Avoid high-cardinality labels such as raw customer IDs, email addresses, tokens, or arbitrary URLs.

### Logs
- Structured JSON logs.
- Include timestamp, severity, service, environment, trace ID, and correlation ID.
- Redact tokens, credentials, and sensitive fields.
- Keep error messages useful without exposing stack traces to API consumers.

### Traces
- Propagate W3C Trace Context across HTTP and messaging.
- Add spans for gateway-to-service, database, broker, and connector calls.
- Record safe attributes only; do not attach full sensitive payloads.

## Health checks

- Liveness: process can continue running.
- Readiness: service can accept traffic and required dependencies are available.
- Dependency health endpoints should distinguish degraded dependencies from process failure.

## Initial SLO hypotheses

These are learning targets, not production promises:
- API availability target: 99.5% in local test scenarios.
- p95 latency target: under 300 ms for simple read endpoints under a defined load profile.
- Integration workflow success rate: measured by workflow type and excluding explicitly rejected invalid input.
Set meaningful targets only after establishing repeatable test workloads.

## Incident/runbook template

For each alert, document:
1. Symptom and impact.
2. Dashboard and relevant queries.
3. First diagnostic steps.
4. Safe mitigation.
5. Recovery verification.
6. Follow-up and prevention issue.

## Performance testing

Use a repeatable k6 scenario with documented data volume, concurrency, duration, environment, and thresholds. Report p50/p95/p99, throughput, error rate, and resource utilization. Do not compare results across materially different environments as if they were equivalent.
