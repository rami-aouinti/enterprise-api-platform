<?php

declare(strict_types=1);

namespace App\ApiRegistry\Infrastructure;

use App\ApiRegistry\Application\ApiRegistryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use JsonException;
use RuntimeException;

final readonly class DoctrineApiRegistryRepository implements ApiRegistryRepository
{
    public function __construct(private Connection $connection) {}
    public function findAll(int $limit, int $offset, ?string $search, ?string $state, ?string $tag): array
    {
        [$where, $parameters] = $this->filters($search, $state, $tag);
        $rows = $this->connection->executeQuery(sprintf('SELECT id, name, description, owner, tags, lifecycle_state, created_at, updated_at FROM api_registry %s ORDER BY lower(name) ASC, id ASC LIMIT :limit OFFSET :offset', $where), [...$parameters, 'limit' => $limit, 'offset' => $offset], ['limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER])->fetchAllAssociative();
        return array_map($this->hydrateApi(...), $rows);
    }
    public function count(?string $search, ?string $state, ?string $tag): int
    {
        [$where, $parameters] = $this->filters($search, $state, $tag);
        return (int) $this->connection->fetchOne(sprintf('SELECT COUNT(*) FROM api_registry %s', $where), $parameters);
    }
    public function find(string $id): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT id, name, description, owner, tags, lifecycle_state, created_at, updated_at FROM api_registry WHERE id = :id', ['id' => $id]);
        if ($row === false) {
            return null;
        }
        $api = $this->hydrateApi($row);
        $versions = $this->connection->fetchAllAssociative('SELECT id, api_id, version, created_at FROM api_registry_versions WHERE api_id = :api_id ORDER BY created_at DESC, version DESC', ['api_id' => $id]);
        $api['versions'] = array_map($this->hydrateVersionSummary(...), $versions);
        return $api;
    }
    public function create(array $api): array
    {
        $row = $this->connection->fetchAssociative('INSERT INTO api_registry (id, name, description, owner, tags, lifecycle_state) VALUES (:id, :name, :description, :owner, CAST(:tags AS JSONB), :lifecycle_state) RETURNING id, name, description, owner, tags, lifecycle_state, created_at, updated_at', ['id' => self::generateUuid(), 'name' => $api['name'], 'description' => $api['description'], 'owner' => $api['owner'], 'tags' => json_encode($api['tags'], JSON_THROW_ON_ERROR), 'lifecycle_state' => $api['lifecycle_state']]);
        if ($row === false) {
            throw new RuntimeException('The API registry insert did not return a record.');
        }
        return $this->hydrateApi($row);
    }
    public function update(string $id, array $api): ?array
    {
        $assignments = [];
        $parameters = ['id' => $id];
        foreach ($api as $field => $value) {
            if (!in_array($field, ['name', 'description', 'owner', 'lifecycle_state', 'tags'], true)) {
                continue;
            }
            if ($field === 'tags') {
                $assignments[] = 'tags = CAST(:tags AS JSONB)';
                $parameters['tags'] = json_encode($value, JSON_THROW_ON_ERROR);
            } else {
                $assignments[] = sprintf('%s = :%s', $field, $field);
                $parameters[$field] = $value;
            }
        }
        if ($assignments === []) {
            return $this->find($id);
        }
        $assignments[] = 'updated_at = CURRENT_TIMESTAMP';
        $row = $this->connection->fetchAssociative(sprintf('UPDATE api_registry SET %s WHERE id = :id RETURNING id, name, description, owner, tags, lifecycle_state, created_at, updated_at', implode(', ', $assignments)), $parameters);
        return $row === false ? null : $this->hydrateApi($row);
    }
    public function addVersion(string $apiId, string $version, array $document): array
    {
        $row = $this->connection->fetchAssociative('INSERT INTO api_registry_versions (id, api_id, version, openapi_document) VALUES (:id, :api_id, :version, CAST(:document AS JSONB)) RETURNING id, api_id, version, openapi_document, created_at', ['id' => self::generateUuid(), 'api_id' => $apiId, 'version' => $version, 'document' => json_encode($document, JSON_THROW_ON_ERROR)]);
        if ($row === false) {
            throw new RuntimeException('The API version insert did not return a record.');
        }
        return $this->hydrateVersion($row);
    }
    public function findVersion(string $apiId, string $version): ?array
    {
        $row = $this->connection->fetchAssociative('SELECT id, api_id, version, openapi_document, created_at FROM api_registry_versions WHERE api_id = :api_id AND version = :version', ['api_id' => $apiId, 'version' => $version]);
        return $row === false ? null : $this->hydrateVersion($row);
    }
    /** @return array{0: string, 1: array<string, string>} */
    private function filters(?string $search, ?string $state, ?string $tag): array
    {
        $conditions = [];
        $parameters = [];
        if ($search !== null && trim($search) !== '') {
            $pattern = '%' . trim($search) . '%';
            $conditions[] = '(name ILIKE :name_search OR description ILIKE :description_search OR owner ILIKE :owner_search)';
            $parameters['name_search'] = $pattern;
            $parameters['description_search'] = $pattern;
            $parameters['owner_search'] = $pattern;
        }
        if ($state !== null && $state !== '') {
            $conditions[] = 'lifecycle_state = :lifecycle_state';
            $parameters['lifecycle_state'] = $state;
        }
        if ($tag !== null && $tag !== '') {
            $conditions[] = 'tags @> CAST(:tag AS JSONB)';
            $parameters['tag'] = json_encode([$tag], JSON_THROW_ON_ERROR);
        }
        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $parameters];
    }
    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function hydrateApi(array $row): array
    {
        return ['id' => (string) $row['id'], 'name' => (string) $row['name'], 'description' => (string) $row['description'], 'owner' => (string) $row['owner'], 'tags' => $this->decodeJson((string) $row['tags']), 'lifecycle_state' => (string) $row['lifecycle_state'], 'created_at' => new DateTimeImmutable((string) $row['created_at'])->format(DATE_ATOM), 'updated_at' => new DateTimeImmutable((string) $row['updated_at'])->format(DATE_ATOM)];
    }
    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function hydrateVersion(array $row): array
    {
        return ['id' => (string) $row['id'], 'api_id' => (string) $row['api_id'], 'version' => (string) $row['version'], 'document' => $this->decodeJson((string) $row['openapi_document']), 'created_at' => new DateTimeImmutable((string) $row['created_at'])->format(DATE_ATOM)];
    }
    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function hydrateVersionSummary(array $row): array
    {
        return ['id' => (string) $row['id'], 'version' => (string) $row['version'], 'created_at' => new DateTimeImmutable((string) $row['created_at'])->format(DATE_ATOM)];
    }
    /** @return array<string, mixed> */
    private function decodeJson(string $json): array
    {
        try {
            $value = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Stored API registry JSON is invalid.', previous: $exception);
        }
        if (!is_array($value)) {
            throw new RuntimeException('Stored API registry JSON must be an object or array.');
        }
        return $value;
    }
    private static function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }
}
