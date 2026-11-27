# ADR-0004: Separate Responsibilities for RabbitMQ and Kafka

- **Status:** Proposed
- **Date:** 2026-11-27
- **Related issue:** [#12](https://github.com/rami-aouinti/enterprise-api-platform/issues/12)

## Context
The platform needs asynchronous task processing and domain event distribution. Using one broker for every pattern can obscure delivery semantics; operating two brokers without distinct responsibilities adds unjustified complexity.

## Decision
Use RabbitMQ for task/work-queue messaging, command-like asynchronous jobs, acknowledgements, bounded retries, and dead-letter queues.

Use Kafka only for durable append-oriented domain event streams where multiple independent consumer groups, retention, replay, or stream processing provide a clear benefit.

Do not publish every message to both systems by default. Select the broker based on the communication requirement and document delivery, ordering, retention, and replay semantics.

Assume at-least-once delivery. Consumers must be idempotent. For database state changes that publish events, use a transactional outbox or equivalent reliable publication mechanism. Do not promise exactly-once business processing.

## Alternatives considered
1. RabbitMQ only: simpler operations and sufficient for work queues, but weaker fit for retained/replayable event streams.
2. Kafka only: powerful event log, but unnecessarily complex for simple task queues.
3. Both brokers for all messages: rejected because it duplicates operational and consistency costs without a defined need.

## Consequences
**Positive:** clear separation between task distribution and event-stream use cases; explicit delivery guarantees; replay and independent consumers where justified.

**Negative:** two broker technologies increase local resource use; teams must understand separate routing, retention, retry, and failure semantics.

## Security and operations
Use authenticated broker connections and least-privilege users, vhosts, and topics. Bound message size and retention. Monitor queue depth, consumer lag, retries, and dead-letter volume. Avoid sensitive personal data in events unless necessary and protected.

## Review criteria
Revisit whether Kafka is warranted after the first real event-stream use case. If no replay or independent-consumer requirement exists, defer Kafka rather than running it for appearance.
