<?php

declare(strict_types=1);

namespace App\ApiRegistry\Application;

interface ApiRegistryRepository
{
    /** @return list<array<string, mixed>> */
    public function findAll(int $limit, int $offset, ?string $search, ?string $state, ?string $tag): array;
    public function count(?string $search, ?string $state, ?string $tag): int;
    /** @return array<string, mixed>|null */
    public function find(string $id): ?array;
    /** @param array<string, mixed> $api @return array<string, mixed> */
    public function create(array $api): array;
    /** @param array<string, mixed> $api @return array<string, mixed>|null */
    public function update(string $id, array $api): ?array;
    /** @param array<string, mixed> $document @return array<string, mixed> */
    public function addVersion(string $apiId, string $version, array $document): array;
    /** @return array<string, mixed>|null */
    public function findVersion(string $apiId, string $version): ?array;
}
