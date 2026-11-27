# ADR-0007: OpenTelemetry for Platform Observability

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
Requests and messages cross the gateway, domain APIs, brokers, and integration runtime. Separate logs without shared context make it difficult to diagnose latency, failures, retries, and cross-service behavior.

## Decision
Use OpenTelemetry APIs/SDKs and semantic conventions as the instrumentation baseline. Propagate trace context across HTTP and messaging boundaries. Instrument inbound/outbound requests, database operations where supported, and message production/consumption.

For the initial local stack, use Prometheus for metrics, Grafana for dashboards, Loki for logs, and Jaeger for trace exploration. Keep application instrumentation vendor-neutral so exporters and backends can change independently.

Define useful service-level signals: request rate, error rate, latency distributions, saturation/resource use, queue depth/consumer lag, retry and dead-letter counts, and integration execution outcomes.

## Alternatives considered
1. Vendor-specific instrumentation: convenient in some environments, but couples application code to a backend.
2. Logs only: insufficient for latency breakdown and distributed request causality.
3. Metrics only: useful for aggregate health but weak for debugging individual cross-service flows.

## Consequences
**Positive:** correlated traces across synchronous and asynchronous boundaries; portable instrumentation; better operational evidence for performance and reliability decisions.

**Negative:** instrumentation and collector configuration add complexity; high-cardinality attributes and sensitive data create cost/privacy risks; sampling and retention need deliberate defaults.

## Security and operations
Never record bearer tokens, passwords, API keys, or unrestricted personal data in telemetry. Redact sensitive attributes, control label cardinality, configure sampling and retention, and restrict access to logs/traces. Monitor collector health and dropped telemetry.

## Review criteria
Revisit exporters and backends after the first instrumented end-to-end flow and validate that the stack provides actionable signals without excessive local resource use.
