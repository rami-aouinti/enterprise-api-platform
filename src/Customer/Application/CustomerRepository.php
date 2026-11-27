<?php

declare(strict_types=1);

namespace App\Customer\Application;

interface CustomerRepository
{
    /** @return list<array<string, mixed>> */
    public function findAll(int $limit, int $offset): array;

    public function count(): int;

    /** @return array<string, mixed>|null */
    public function find(string $id): ?array;

    /** @param array<string, mixed> $customer
     *  @return array<string, mixed>
     */
    public function create(array $customer): array;

    /** @param array<string, mixed> $customer
     *  @return array<string, mixed>|null
     */
    public function update(string $id, array $customer): ?array;
}
