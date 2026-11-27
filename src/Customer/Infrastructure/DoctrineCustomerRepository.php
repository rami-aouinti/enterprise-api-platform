<?php

declare(strict_types=1);

namespace App\Customer\Infrastructure;

use App\Customer\Application\CustomerRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final readonly class DoctrineCustomerRepository implements CustomerRepository
{
    public function __construct(private Connection $connection) {}

    public function findAll(int $limit, int $offset): array
    {
        $rows = $this->connection->executeQuery(
            <<<'SQL'
                SELECT id, first_name, last_name, email, phone, status, created_at, updated_at
                FROM customers
                ORDER BY created_at DESC, id ASC
                LIMIT :limit OFFSET :offset
                SQL,
            ['limit' => $limit, 'offset' => $offset],
            ['limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER],
        )->fetchAllAssociative();

        return array_map($this->hydrate(...), $rows);
    }

    public function count(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM customers');
    }

    public function find(string $id): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT id, first_name, last_name, email, phone, status, created_at, updated_at FROM customers WHERE id = :id',
            ['id' => $id],
        );

        return $row === false ? null : $this->hydrate($row);
    }

    public function create(array $customer): array
    {
        $row = $this->connection->fetchAssociative(
            <<<'SQL'
                INSERT INTO customers (id, first_name, last_name, email, phone, status)
                VALUES (:id, :first_name, :last_name, :email, :phone, :status)
                RETURNING id, first_name, last_name, email, phone, status, created_at, updated_at
                SQL,
            [
                'id' => self::generateUuid(),
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'status' => $customer['status'],
            ],
        );

        return $this->hydrate($row);
    }

    public function update(string $id, array $customer): ?array
    {
        $assignments = [];
        $parameters = ['id' => $id];

        foreach ($customer as $field => $value) {
            if (!in_array($field, ['first_name', 'last_name', 'email', 'phone', 'status'], true)) {
                continue;
            }

            $assignments[] = sprintf('%s = :%s', $field, $field);
            $parameters[$field] = $value;
        }

        if ($assignments === []) {
            return $this->find($id);
        }

        $assignments[] = 'updated_at = CURRENT_TIMESTAMP';
        $row = $this->connection->fetchAssociative(
            sprintf(
                'UPDATE customers SET %s WHERE id = :id RETURNING id, first_name, last_name, email, phone, status, created_at, updated_at',
                implode(', ', $assignments),
            ),
            $parameters,
        );

        return $row === false ? null : $this->hydrate($row);
    }

    /** @param array<string, mixed> $row
     *  @return array<string, mixed>
     */
    private function hydrate(array $row): array
    {
        return [
            'id' => (string) $row['id'],
            'first_name' => (string) $row['first_name'],
            'last_name' => (string) $row['last_name'],
            'email' => (string) $row['email'],
            'phone' => $row['phone'] === null ? null : (string) $row['phone'],
            'status' => (string) $row['status'],
            'created_at' => (new DateTimeImmutable((string) $row['created_at']))->format(DATE_ATOM),
            'updated_at' => (new DateTimeImmutable((string) $row['updated_at']))->format(DATE_ATOM),
        ];
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
